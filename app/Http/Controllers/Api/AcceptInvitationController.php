<?php

namespace App\Http\Controllers\Api;

use App\Actions\Corporate\AcceptInvitation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AcceptInvitationRequest;
use App\Http\Resources\SponsoredUserResource;
use App\Models\Sponsorship;
use Illuminate\Http\JsonResponse;

class AcceptInvitationController extends Controller
{
    /**
     * Activate a seat. The invite token is the credential: whoever holds it
     * is the employee it was sent to. This is the moment the subscription,
     * and the company's bill for it, begins.
     */
    public function __invoke(AcceptInvitationRequest $request, AcceptInvitation $acceptInvitation): JsonResponse
    {
        $sponsorship = Sponsorship::where('invite_token', $request->string('token'))->firstOrFail();

        $user = $acceptInvitation->handle(
            $sponsorship,
            name: $request->string('name')->value(),
            password: $request->filled('password') ? $request->string('password')->value() : null,
        );

        return (new SponsoredUserResource($sponsorship->load(['plan', 'user'])))
            // Stands in for the session a real login flow would create.
            ->additional(['api_token' => $user->api_token])
            ->response();
    }
}
