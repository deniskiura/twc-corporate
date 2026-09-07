<?php

namespace App\Actions\Corporate;

use App\Enums\CreditTransactionType;
use App\Enums\SponsorshipStatus;
use App\Exceptions\SeatStateException;
use App\Models\Sponsorship;
use App\Models\Subscription;
use App\Support\BillingCycle;
use Illuminate\Support\Facades\DB;

class ResumeEmployee
{
    public function __construct(private readonly StartSubscription $startSubscription) {}

    /**
     * Put a suspended seat back to work.
     *
     * Resumed in the same month it was suspended, the seat gets back exactly
     * what the suspension took, which may be nothing if the credits were
     * already used, and nothing new is granted. Resumed in a later month,
     * nothing was granted this month, so a fresh prorated allowance starts
     * just as it would for a new seat.
     */
    public function handle(Sponsorship $sponsorship): Sponsorship
    {
        throw_unless($sponsorship->isSuspended(), SeatStateException::notSuspended());

        return DB::transaction(function () use ($sponsorship) {
            $suspendedThisCycle = $sponsorship->suspended_at !== null
                && BillingCycle::current()->contains($sponsorship->suspended_at);

            $suspended = $sponsorship->subscriptions()->whereNotNull('ended_at')->latest('ended_at')->first();

            $sponsorship->forceFill([
                'status' => SponsorshipStatus::Joined,
                'suspended_at' => null,
            ])->save();

            $resumed = $this->startSubscription->handle($sponsorship, grantAllowance: ! $suspendedThisCycle);

            if ($suspendedThisCycle && $suspended) {
                $this->restoreWhatSuspensionTook($sponsorship, $suspended, $resumed);
            }

            return $sponsorship;
        });
    }

    /**
     * Reverse the expiry the suspension wrote when it closed the previous
     * subscription. Only expiries from that moment count, so credits taken
     * back earlier in the month for other reasons stay taken back.
     */
    private function restoreWhatSuspensionTook(Sponsorship $sponsorship, Subscription $suspended, Subscription $resumed): void
    {
        $takenBack = (int) $sponsorship->user->creditTransactions()
            ->where('subscription_id', $suspended->id)
            ->where('type', CreditTransactionType::Expiry)
            ->where('occurred_at', '>=', $suspended->ended_at)
            ->where('amount', '<', 0)
            ->sum('amount');

        if ($takenBack === 0) {
            return;
        }

        $sponsorship->user->creditTransactions()->create([
            'subscription_id' => $resumed->id,
            'type' => CreditTransactionType::Expiry,
            'amount' => -$takenBack,
            'occurred_at' => now(),
            'note' => 'Suspension lifted, allowance restored',
        ]);
    }
}
