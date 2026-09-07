<?php

namespace App\Actions\Corporate;

use App\Enums\SponsorshipStatus;
use App\Mail\EmployeeInvitation;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Sponsorship;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteEmployee
{
    /**
     * Reserve a seat for an employee and email them the link to accept it.
     * Nothing is billed until they do.
     *
     * The email is sent inside the transaction and synchronously, so if it
     * can't go out the admin finds out now rather than a seat sitting there
     * with an invite nobody received.
     */
    public function handle(Company $company, User $invitedBy, string $email, Plan $plan): Sponsorship
    {
        return DB::transaction(function () use ($company, $invitedBy, $email, $plan) {
            $sponsorship = $company->sponsorships()->create([
                'plan_id' => $plan->id,
                'invited_by_id' => $invitedBy->id,
                'email' => Str::lower($email),
                'invite_token' => Sponsorship::generateToken(),
                'status' => SponsorshipStatus::Invited,
                'invited_at' => now(),
                'last_sent_at' => now(),
            ]);

            Mail::to($sponsorship->email)->send(new EmployeeInvitation($sponsorship));

            return $sponsorship;
        });
    }
}
