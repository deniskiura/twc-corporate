<?php

namespace App\Actions\Corporate;

use App\Enums\SponsorshipStatus;
use App\Exceptions\InviteStateException;
use App\Models\Sponsorship;

class RevokeInvitation
{
    /**
     * Withdraw an invite that hasn't been accepted. The row is kept for the
     * audit trail, and the same email can be invited again later.
     */
    public function handle(Sponsorship $sponsorship): void
    {
        throw_unless($sponsorship->isPending(), InviteStateException::notPending());

        $sponsorship->forceFill([
            'status' => SponsorshipStatus::Revoked,
            'revoked_at' => now(),
        ])->save();
    }
}
