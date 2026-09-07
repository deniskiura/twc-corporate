<?php

namespace App\Actions\Corporate;

use App\Exceptions\InviteStateException;
use App\Models\Sponsorship;

class ResendInvitation
{
    /**
     * Rotate the token and restart the "pending since" clock. The old link
     * stops working, which matters if an invite has been forwarded around.
     */
    public function handle(Sponsorship $sponsorship): Sponsorship
    {
        throw_unless($sponsorship->isPending(), InviteStateException::notPending());

        $sponsorship->forceFill([
            'invite_token' => Sponsorship::generateToken(),
            'last_sent_at' => now(),
        ])->save();

        return $sponsorship;
    }
}
