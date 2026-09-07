<?php

namespace App\Actions\Corporate;

use App\Enums\SponsorshipStatus;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Sponsorship;
use App\Models\User;
use Illuminate\Support\Str;

class InviteEmployee
{
    /**
     * Reserve a seat for an employee. Nothing is billed until they accept.
     *
     * Email is mocked for this exercise: instead of sending the invite, the
     * token is returned to the caller so the link can be shared by hand.
     */
    public function handle(Company $company, User $invitedBy, string $email, Plan $plan): Sponsorship
    {
        return $company->sponsorships()->create([
            'plan_id' => $plan->id,
            'invited_by_id' => $invitedBy->id,
            'email' => Str::lower($email),
            'invite_token' => Sponsorship::generateToken(),
            'status' => SponsorshipStatus::Invited,
            'invited_at' => now(),
            'last_sent_at' => now(),
        ]);
    }
}
