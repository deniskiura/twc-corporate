# Product improvements

Two things I would raise with the founder and the designer before building more of the spec as written. Estimates assume one engineer on this codebase.

## 1. Dormant seats: stop billing companies for employees who never come back

**The problem.** The rule "a subscription starts when the employee first logs in" is right, but it only protects the company on the way in. An employee who logs in once in March and never books anything is still a full-price seat in September. Companies notice this at renewal, and "we were paying for people who didn't use it" is the standard reason a wellness perk gets cut. It hurts us too: churn at renewal is far more expensive than a paused seat.

**Who it's for.** The company buying it (they stop paying for ghosts), and us (they renew).

**What to build.**

- A "last booked" date per seat, from the credit ledger. Free, the data is there.
- Flag seats with no bookings for two full cycles as dormant on the team screen, the same way stale invites are flagged today.
- Let the admin pause a dormant seat: the subscription closes, no allowance is granted, nothing is billed. Resume re-opens it with a prorated month, reusing the accept logic.
- Nudge the employee before the admin sees the flag, so the perk gets a second chance.

**Cost.** About 4 days. Last-booked and the dormant flag: half a day. Pause and resume with the billing rules: a day and a half. Team screen states and the admin action: one day. The nudge email and a scheduled job: half a day. Testing the date edges: half a day.

## 2. The per-seat plan may be the wrong unit. Consider a company credit pool with per-person caps

**The problem.** The pitch says "companies buy credits and give them to employees" and "the flexibility is the product". The spec then bills a fixed plan per seat, which is the gym-membership model with a different logo: the heavy user hits the ceiling in week two, the light user's credits expire, and the admin is asked to pick a plan for someone they have never seen use the app. Beta Bank in the seed data is this exactly: two seats, both at 100%, nothing the admin can do about it this month.

**Who it's for.** The company (they pay for usage, not for guesses), the employee (their credits aren't capped by a plan picked before they joined), and us (a pool is a much easier upsell than moving individuals between plans).

**What to build.**

- A monthly credit pool bought by the company, with a default per-person cap the admin can raise for individuals.
- Employees draw from the pool; the ledger already supports it, each spend just references the pool instead of a seat subscription.
- Billing becomes pool size plus overage, instead of seat lines. The team screen's summary becomes pool used versus pool size, which is the number the admin actually asks about.
- Keep per-seat plans for companies that want a fixed price per head, so this is an option, not a migration.

**Cost.** One and a half to two weeks. Pool and cap model with ledger changes: two days. Billing and proration for a pool: two days. Admin screen changes, including moving a company between the two models: two to three days. Employee-side display and booking rules against the cap: two days. Testing and a data migration for existing companies: two days. I would ship it behind a flag to two or three pilot companies first, and I would want to see how the exhausted-credit case in the current model actually plays out with real customers before committing all of it.
