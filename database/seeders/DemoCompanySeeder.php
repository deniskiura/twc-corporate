<?php

namespace Database\Seeders;

use App\Actions\Corporate\AcceptInvitation;
use App\Actions\Corporate\InviteEmployee;
use App\Actions\Corporate\RevokeInvitation;
use App\Actions\Corporate\SuspendEmployee;
use App\Enums\CreditTransactionType;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Sponsorship;
use App\Models\User;
use App\Support\BillingCycle;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Mail;

/**
 * Three companies that between them cover every state the team screen has
 * to handle: a busy team with a stale invite, a team that has used all of
 * its credits, and a brand-new company with nobody invited yet.
 *
 * Seats are created through the real invite and accept actions so the seed
 * data is shaped exactly like production data would be.
 */
class DemoCompanySeeder extends Seeder
{
    private const ACTIVITIES = [
        'Reformer Pilates, Lavington',
        'Lap swim, Karen',
        'HIIT class, Westlands',
        'Yoga flow, Kilimani',
        'Boxing session, Parklands',
        'Spin class, Upper Hill',
    ];

    public function __construct(
        private readonly InviteEmployee $inviteEmployee,
        private readonly AcceptInvitation $acceptInvitation,
        private readonly RevokeInvitation $revokeInvitation,
        private readonly SuspendEmployee $suspendEmployee,
    ) {}

    public function run(): void
    {
        // The real invite action emails people. Demo seats shouldn't.
        Mail::fake();

        $lite = Plan::where('name', 'Lite')->firstOrFail();
        $standard = Plan::where('name', 'Standard')->firstOrFail();
        $plus = Plan::where('name', 'Plus')->firstOrFail();

        // Acme: a working team with one seat out of credits, one suspended,
        // and one invite that has sat unanswered for three weeks.
        $acme = Company::create(['name' => 'Acme Logistics']);
        $amina = $this->admin($acme, 'Amina Njoroge', 'amina@acme.test', 'acme-admin-token');

        $brian = $this->join($this->invite($acme, $amina, 'brian@acme.test', $standard, daysAgo: 90), 'Brian Otieno', daysAgo: 84);
        $this->spend($brian, 9);

        $cynthia = $this->join($this->invite($acme, $amina, 'cynthia@acme.test', $plus, daysAgo: 40), 'Cynthia Wanjiru', daysAgo: 36);
        $this->spend($cynthia, 30);

        $david = $this->join($this->invite($acme, $amina, 'david@acme.test', $lite, daysAgo: 6), 'David Kimani', daysAgo: 4);
        $this->spend($david, 2);

        $kevin = $this->invite($acme, $amina, 'kevin@acme.test', $lite, daysAgo: 50);
        $this->on(now()->subDays(45), fn () => $this->acceptInvitation->handle($kevin, 'Kevin Mwangi', 'password'));
        $this->on(now()->subDays(12), fn () => $this->suspendEmployee->handle($kevin->refresh()));

        $this->invite($acme, $amina, 'esther@acme.test', $standard, daysAgo: 23);
        $this->invite($acme, $amina, 'faith@acme.test', $lite, daysAgo: 2);

        $george = $this->invite($acme, $amina, 'george@acme.test', $standard, daysAgo: 49);
        $this->on(now()->subDays(37), fn () => $this->revokeInvitation->handle($george));

        // Beta Bank: every joined seat has used its full allowance this month.
        $beta = Company::create(['name' => 'Beta Bank']);
        $bob = $this->admin($beta, 'Bob Mutua', 'bob@betabank.test', 'beta-admin-token');

        $hannah = $this->join($this->invite($beta, $bob, 'hannah@betabank.test', $lite, daysAgo: 60), 'Hannah Njeri', daysAgo: 59);
        $this->spend($hannah, 8);

        $ian = $this->join($this->invite($beta, $bob, 'ian@betabank.test', $lite, daysAgo: 60), 'Ian Odhiambo', daysAgo: 59);
        $this->spend($ian, 8);

        $this->invite($beta, $bob, 'joy@betabank.test', $standard, daysAgo: 8);

        // Cedar Studio: just signed up, nobody invited yet.
        $cedar = Company::create(['name' => 'Cedar Studio']);
        $this->admin($cedar, 'Carol Akinyi', 'carol@cedar.test', 'cedar-admin-token');
    }

    private function admin(Company $company, string $name, string $email, string $apiToken): User
    {
        return User::factory()->companyAdmin($company)->create([
            'name' => $name,
            'email' => $email,
            'api_token' => $apiToken,
        ]);
    }

    private function invite(Company $company, User $admin, string $email, Plan $plan, int $daysAgo): Sponsorship
    {
        return $this->on(
            now()->subDays($daysAgo),
            fn () => $this->inviteEmployee->handle($company, $admin, $email, $plan),
        );
    }

    private function join(Sponsorship $sponsorship, string $name, int $daysAgo): User
    {
        $joinedAt = now()->subDays($daysAgo);

        $user = $this->on($joinedAt, fn () => $this->acceptInvitation->handle($sponsorship, $name, 'password'));

        $this->grantCurrentMonthAllowance($sponsorship, $user, $joinedAt);

        return $user;
    }

    /**
     * The month-end billing job that tops allowances up isn't built in this
     * exercise, so seats that joined in an earlier month get this month's
     * allowance here instead.
     */
    private function grantCurrentMonthAllowance(Sponsorship $sponsorship, User $user, CarbonImmutable $joinedAt): void
    {
        $cycle = BillingCycle::current();

        if ($cycle->contains($joinedAt)) {
            return;
        }

        $user->creditTransactions()->create([
            'subscription_id' => $sponsorship->currentSubscription?->id,
            'type' => CreditTransactionType::Allowance,
            'amount' => $sponsorship->plan->monthly_credits,
            'occurred_at' => $cycle->start,
            'note' => 'Monthly allowance for '.$cycle->start->format('F Y'),
        ]);
    }

    /**
     * Book classes this month until the given number of credits is spent.
     */
    private function spend(User $user, int $credits): void
    {
        $from = $user->sponsorship?->joined_at?->max(BillingCycle::current()->start) ?? BillingCycle::current()->start;

        while ($credits > 0) {
            $amount = min($credits, fake()->numberBetween(1, 3));

            $user->creditTransactions()->create([
                'subscription_id' => $user->sponsorship?->currentSubscription?->id,
                'type' => CreditTransactionType::Spend,
                'amount' => -$amount,
                'occurred_at' => fake()->dateTimeBetween($from, 'now'),
                'note' => fake()->randomElement(self::ACTIVITIES),
            ]);

            $credits -= $amount;
        }
    }

    /**
     * Run a callback as if it were the given moment, so the actions stamp
     * realistic timestamps without needing backdating parameters.
     */
    private function on(CarbonImmutable $moment, Closure $callback): mixed
    {
        CarbonImmutable::setTestNow($moment);

        try {
            return $callback();
        } finally {
            CarbonImmutable::setTestNow();
        }
    }
}
