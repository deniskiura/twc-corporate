<?php

namespace App\Actions\Corporate;

use App\Exceptions\SeatStateException;
use App\Mail\EmployeeInvitation;
use App\Models\Sponsorship;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ResendInvitation
{
    /**
     * Rotate the token, restart the "pending since" clock and email a fresh
     * link. The old link stops working, which matters if an invite has been
     * forwarded around.
     */
    public function handle(Sponsorship $sponsorship): Sponsorship
    {
        throw_unless($sponsorship->isPending(), SeatStateException::notPending());

        return DB::transaction(function () use ($sponsorship) {
            $sponsorship->forceFill([
                'invite_token' => Sponsorship::generateToken(),
                'last_sent_at' => now(),
            ])->save();

            Mail::to($sponsorship->email)->send(new EmployeeInvitation($sponsorship));

            return $sponsorship;
        });
    }
}
