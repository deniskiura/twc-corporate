<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * A seat right after its invite is created or resent: the seat plus the
 * link that was just emailed. This is the only place the token is exposed,
 * so the admin can share it by hand if the email doesn't arrive and the API
 * can be exercised without a mailbox.
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
