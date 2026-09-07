<?php

namespace App\Actions\Corporate;

use App\Enums\SponsorshipStatus;
use App\Enums\UserRole;
use App\Exceptions\SeatStateException;
use App\Models\Sponsorship;
use Illuminate\Support\Facades\DB;

class RemoveEmployee
{
    public function __construct(private readonly EndSubscription $endSubscription) {}

    /**
     * Take an employee off the company's plan for good. They keep their TWC
     * account and anything they bought themselves, and the company can
     * invite them again later.
     */
    public function handle(Sponsorship $sponsorship): void
    {
        throw_unless(
            $sponsorship->hasJoined() || $sponsorship->isSuspended(),
            SeatStateException::notJoined(),
        );

        DB::transaction(function () use ($sponsorship) {
            $this->endSubscription->handle($sponsorship, 'Removed from the company plan');

            $sponsorship->forceFill([
                'status' => SponsorshipStatus::Removed,
                'removed_at' => now(),
            ])->save();

            $user = $sponsorship->user;

            // Admins keep their role; everyone else goes back to being a member.
            if ($user && ! $user->isCompanyAdmin()) {
                $user->forceFill(['role' => UserRole::Member, 'company_id' => null])->save();
            }
        });
    }
}
