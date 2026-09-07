import json, urllib.request, urllib.error

BASE = "http://twc.test/api"  # change if not using Herd
ACME, BETA, CEDAR = "acme-admin-token", "beta-admin-token", "cedar-admin-token"

def call(method, path, token=None, body=None):
    data = json.dumps(body).encode() if body is not None else None
    req = urllib.request.Request(BASE + path, data=data, method=method)
    req.add_header("Accept", "application/json")
    req.add_header("Content-Type", "application/json")
    if token:
        req.add_header("Authorization", "Bearer " + token)
    try:
        with urllib.request.urlopen(req) as r:
            raw = r.read().decode()
            return r.status, (json.loads(raw) if raw else None)
    except urllib.error.HTTPError as e:
        raw = e.read().decode()
        try:
            return e.code, json.loads(raw)
        except Exception:
            return e.code, raw[:200]

def check(label, got, want):
    ok = got == want
    print(("PASS" if ok else "FAIL"), label, "->", got if ok else f"got {got}, want {want}")

s, acme = call("GET", "/company/users", ACME)
check("acme list status", s, 200)
check("acme team size", len(acme["data"]), 5)
print("     acme totals:", json.dumps(acme["meta"]["totals"]), "| cycle:", acme["meta"]["billing_cycle"])
for u in acme["data"]:
    print("     ", u["email"], u["status"], "plan=" + u["plan"]["name"], "credits=" + json.dumps(u["credits"]), "pending=" + str(u["days_pending"]), "stale=" + str(u["is_stale"]))
check("acme has no revoked rows", any(u["email"] == "george@acme.test" for u in acme["data"]), False)
check("acme response hides invite tokens", any("invite_token" in u for u in acme["data"]), False)

s, body = call("GET", "/company/users")
check("no token -> 401", s, 401)
s, body = call("GET", "/company/users", "not-a-real-token")
check("bad token -> 401", s, 401)

s, beta = call("GET", "/company/users", BETA)
check("beta sees only its own 3 seats", sorted(u["email"] for u in beta["data"]), ["hannah@betabank.test", "ian@betabank.test", "joy@betabank.test"])
check("beta credits exhausted flag", beta["meta"]["totals"]["credits"]["exhausted"], True)

s, cedar = call("GET", "/company/users", CEDAR)
check("cedar empty team", (s, cedar["data"], cedar["meta"]["totals"]["joined"]), (200, [], 0))

# Invite as Acme, trying to smuggle in another company's id.
plans_standard = next(u["plan"]["id"] for u in acme["data"] if u["plan"]["name"] == "Standard")
s, inv = call("POST", "/company/invites", ACME, {"email": "  Newbie@Acme.TEST ", "plan_id": plans_standard, "company_id": beta["meta"]["company"]["id"]})
check("invite created", s, 201)
check("invite email normalised", inv["data"]["email"], "newbie@acme.test")
check("invite token returned", bool(inv["data"].get("invite_token")) and inv["data"]["invite_url"].endswith(inv["data"]["invite_token"]), True)
s, again = call("GET", "/company/users", ACME)
check("invite landed in Acme, not Beta", any(u["email"] == "newbie@acme.test" for u in again["data"]), True)
s, b2 = call("GET", "/company/users", BETA)
check("beta unchanged", len(b2["data"]), 3)

s, dup = call("POST", "/company/invites", ACME, {"email": "newbie@acme.test", "plan_id": plans_standard})
check("duplicate invite -> 422", s, 422)
print("     ", dup.get("message"))
s, badplan = call("POST", "/company/invites", ACME, {"email": "x@acme.test", "plan_id": 999})
check("unknown plan -> 422", s, 422)
s, noemail = call("POST", "/company/invites", ACME, {"email": "not-an-email", "plan_id": plans_standard})
check("bad email -> 422", s, 422)

new_id = inv["data"]["id"]
s, _ = call("POST", f"/company/invites/{new_id}/resend", BETA)
check("beta resending acme invite -> 404", s, 404)
s, _ = call("DELETE", f"/company/invites/{new_id}", BETA)
check("beta revoking acme invite -> 404", s, 404)
s, resent = call("POST", f"/company/invites/{new_id}/resend", ACME)
check("acme resend -> 200 with new token", (s, resent["data"]["invite_token"] != inv["data"]["invite_token"]), (200, True))

# Accept with the *old* token must fail, the new one must work.
s, old = call("POST", "/invites/accept", None, {"token": inv["data"]["invite_token"], "name": "New Person"})
check("old token after resend -> 404", s, 404)
s, joined = call("POST", "/invites/accept", None, {"token": resent["data"]["invite_token"], "name": "New Person", "password": "password123"})
check("accept -> 200 joined", (s, joined["data"]["status"]), (200, "joined"))
print("     joined_at:", joined["data"]["joined_at"], "credits:", json.dumps(joined["data"]["credits"]), "api_token:", (joined.get("api_token") or "")[:8] + "...")
s, twice = call("POST", "/invites/accept", None, {"token": resent["data"]["invite_token"], "name": "New Person"})
check("accept twice -> 409", s, 409)
s, _ = call("POST", "/invites/accept", None, {"token": "nope", "name": "X"})
check("unknown token -> 404", s, 404)
s, _ = call("POST", "/invites/accept", None, {"name": "X"})
check("missing token -> 422", s, 422)

# An employee's token must not open the admin API.
s, _ = call("GET", "/company/users", joined["api_token"])
check("employee token on admin API -> 403", s, 403)

# Staff see everything in the console but are not a company admin.
s, _ = call("GET", "/company/users", "twc-staff-token")
check("staff token on company API -> 403", s, 403)

# Revoke a pending invite, then the link must be dead.
s, inv2 = call("POST", "/company/invites", ACME, {"email": "leaver@acme.test", "plan_id": plans_standard})
s, _ = call("DELETE", f"/company/invites/{inv2['data']['id']}", ACME)
check("revoke -> 204", s, 204)
s, _ = call("POST", "/invites/accept", None, {"token": inv2["data"]["invite_token"], "name": "Leaver"})
check("accept revoked -> 410", s, 410)
s, _ = call("DELETE", f"/company/invites/{inv2['data']['id']}", ACME)
check("revoke twice -> 409", s, 409)
s, reinv = call("POST", "/company/invites", ACME, {"email": "leaver@acme.test", "plan_id": plans_standard})
check("re-invite after revoke -> 201", s, 201)
s, _ = call("POST", f"/company/invites/{joined['data']['id']}/resend", ACME)
check("resend a joined seat -> 409", s, 409)

# Suspend, resume and remove: joined seats only, and only your own company's.
s, team = call("GET", "/company/users", ACME)
david = next(u for u in team["data"] if u["email"] == "david@acme.test")
pending = next(u for u in team["data"] if u["status"] == "invited")
s, _ = call("POST", f"/company/users/{david['id']}/suspend", BETA)
check("beta suspending acme seat -> 404", s, 404)
s, _ = call("POST", f"/company/users/{pending['id']}/suspend", ACME)
check("suspend an invited seat -> 409", s, 409)
s, _ = call("POST", f"/company/users/{david['id']}/resume", ACME)
check("resume a joined seat -> 409", s, 409)
s, sus = call("POST", f"/company/users/{david['id']}/suspend", ACME)
check("suspend -> suspended, nothing left to spend", (s, sus["data"]["status"], sus["data"]["credits"]["remaining"]), (200, "suspended", 0))
s, team = call("GET", "/company/users", ACME)
check("totals count the suspended seat", team["meta"]["totals"]["suspended"], 1)
s, res = call("POST", f"/company/users/{david['id']}/resume", ACME)
check("resume same month -> joined, credits restored, nothing granted twice",
      (s, res["data"]["status"], res["data"]["credits"]["remaining"], res["data"]["credits"]["allowance"]),
      (200, "joined", david["credits"]["remaining"], david["credits"]["allowance"]))
s, _ = call("DELETE", f"/company/users/{pending['id']}", ACME)
check("remove an invited seat -> 409 (withdraw it instead)", s, 409)
s, _ = call("DELETE", f"/company/users/{david['id']}", ACME)
check("remove -> 204", s, 204)
s, team = call("GET", "/company/users", ACME)
check("removed seat gone from the list", any(u["id"] == david["id"] for u in team["data"]), False)
s, reinv = call("POST", "/company/invites", ACME, {"email": "david@acme.test", "plan_id": plans_standard})
check("re-invite a removed employee -> 201", s, 201)
s, rejoin = call("POST", "/invites/accept", None, {"token": reinv["data"]["invite_token"], "name": "ignored, account exists"})
check("re-accept links the same person", (s, rejoin["data"]["name"]), (200, "David Kimani"))
