<?php

namespace App\Actions\Corporate;

use App\Enums\SponsorshipStatus;
use App\Exceptions\SeatStateException;
use App\Models\Sponsorship;
use Illuminate\Support\Facades\DB;

class SuspendEmployee
{
    public function __construct(private readonly EndSubscription $endSubscription) {}

    /**
     * Pause a seat. Billing stops after this month and the rest of this
     * month's allowance is taken back, but the employee stays on the team
     * list and can be resumed at any time.
     */
    public function handle(Sponsorship $sponsorship): Sponsorship
    {
        throw_unless($sponsorship->hasJoined(), SeatStateException::notJoined());

        return DB::transaction(function () use ($sponsorship) {
            $this->endSubscription->handle($sponsorship, 'Seat suspended by the company');

            $sponsorship->forceFill([
                'status' => SponsorshipStatus::Suspended,
                'suspended_at' => now(),
            ])->save();

            return $sponsorship;
        });
    }
}
