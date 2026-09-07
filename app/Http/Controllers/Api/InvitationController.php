<?php

namespace App\Http\Controllers\Api;

use App\Actions\Corporate\InviteEmployee;
use App\Actions\Corporate\ResendInvitation;
use App\Actions\Corporate\RevokeInvitation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InviteEmployeeRequest;
use App\Http\Resources\InvitationResource;
use App\Models\Plan;
use App\Models\Sponsorship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvitationController extends Controller
{
    /**
     * Invite an employee onto a plan. Returns the invite link in place of the
     * email that isn't sent in this exercise.
     */
    public function store(InviteEmployeeRequest $request, InviteEmployee $inviteEmployee): JsonResponse
    {
        $sponsorship = $inviteEmployee->handle(
            company: $request->user()->company,
            invitedBy: $request->user(),
            email: $request->string('email')->value(),
            plan: Plan::findOrFail($request->integer('plan_id')),
        );

        return (new InvitationResource($sponsorship->load('plan')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Issue a fresh link for an invite that is still pending.
     */
    public function resend(Request $request, ResendInvitation $resendInvitation, int $sponsorship): InvitationResource
    {
        $sponsorship = $resendInvitation->handle($this->findInCompany($request, $sponsorship));

        return new InvitationResource($sponsorship->load('plan'));
    }

    /**
     * Withdraw an invite that is still pending.
     */
    public function destroy(Request $request, RevokeInvitation $revokeInvitation, int $sponsorship): Response
    {
        $revokeInvitation->handle($this->findInCompany($request, $sponsorship));

        return response()->noContent();
    }

    /**
     * Look the seat up through the admin's own company rather than globally,
     * so another company's seat is a plain 404 rather than a leak.
     */
    private function findInCompany(Request $request, int $id): Sponsorship
    {
        return $request->user()->company->sponsorships()->findOrFail($id);
    }
}
