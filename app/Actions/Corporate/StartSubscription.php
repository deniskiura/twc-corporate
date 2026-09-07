<?php

namespace App\Actions\Corporate;

use App\Enums\CreditTransactionType;
use App\Models\Sponsorship;
use App\Models\Subscription;
use App\Support\BillingCycle;
use Carbon\CarbonImmutable;

class StartSubscription
{
    /**
     * Open a billable period on the seat's plan, starting now. Used when an
     * invite is accepted and when a suspended seat is resumed.
     *
     * The first month is prorated by the days left in it, so a seat that
     * starts on the 28th neither gets nor pays for a full month.
     */
    public function handle(Sponsorship $sponsorship, bool $grantAllowance = true): Subscription
    {
        $from = CarbonImmutable::now();

        $subscription = $sponsorship->subscriptions()->create([
            'plan_id' => $sponsorship->plan_id,
            'started_at' => $from,
        ]);

        if ($grantAllowance) {
            $cycle = BillingCycle::containing($from);

            $sponsorship->user->creditTransactions()->create([
                'subscription_id' => $subscription->id,
                'type' => CreditTransactionType::Allowance,
                'amount' => $cycle->prorate($sponsorship->plan->monthly_credits, $from),
                'occurred_at' => $from,
                'note' => 'Allowance for the rest of '.$cycle->start->format('F Y'),
            ]);
        }

        return $subscription;
    }
}
