<?php

namespace App\Actions\Corporate;

use App\Enums\SponsorshipStatus;
use App\Enums\UserRole;
use App\Exceptions\SeatStateException;
use App\Models\Sponsorship;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AcceptInvitation
{
    public function __construct(private readonly StartSubscription $startSubscription) {}

    /**
     * Turn a pending invite into a live, billable seat.
     *
     * The subscription starts now rather than when the invite was sent,
     * because the company only pays for employees who actually show up.
     */
    public function handle(Sponsorship $sponsorship, string $name, ?string $password = null): User
    {
        $this->ensureAcceptable($sponsorship);

        return DB::transaction(function () use ($sponsorship, $name, $password) {
            $joinedAt = CarbonImmutable::now();
            $user = $this->findOrCreateUser($sponsorship, $name, $password);

            $user->forceFill([
                // An admin who is also given a seat keeps their admin role.
                'role' => $user->isCompanyAdmin() ? UserRole::CompanyAdmin : UserRole::Employee,
                'company_id' => $sponsorship->company_id,
                'api_token' => $user->api_token ?? Str::random(60),
            ])->save();

            $sponsorship->forceFill([
                'user_id' => $user->id,
                'status' => SponsorshipStatus::Joined,
                'joined_at' => $joinedAt,
            ])->save();

            $this->startSubscription->handle($sponsorship);

            return $user;
        });
    }

    private function ensureAcceptable(Sponsorship $sponsorship): void
    {
        throw_if($sponsorship->status === SponsorshipStatus::Revoked, SeatStateException::revoked());
        throw_if($sponsorship->status === SponsorshipStatus::Joined, SeatStateException::alreadyAccepted());
    }

    private function findOrCreateUser(Sponsorship $sponsorship, string $name, ?string $password): User
    {
        $existing = User::where('email', $sponsorship->email)->first();

        if ($existing) {
            $sponsoredElsewhere = $existing->role === UserRole::Employee
                && $existing->company_id !== $sponsorship->company_id;

            throw_if($sponsoredElsewhere, SeatStateException::sponsoredElsewhere());

            return $existing;
        }

        return User::forceCreate([
            'name' => $name,
            'email' => $sponsorship->email,
            'password' => $password ?? Str::password(),
            // Holding the invite token proves they own the inbox.
            'email_verified_at' => now(),
        ]);
    }
}
