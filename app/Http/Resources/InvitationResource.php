<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * A seat right after its invite is created or resent: the seat plus the
 * one-time link. This is the only place the token is exposed, standing in
 * for the email we don't send in this exercise.
 */
class InvitationResource extends SponsoredUserResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'invite_token' => $this->invite_token,
            'invite_url' => route('invite.show', ['sponsorship' => $this->invite_token]),
        ];
    }
}
