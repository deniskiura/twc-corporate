<?php

namespace App\Actions\Corporate;

use App\Enums\CreditTransactionType;
use App\Models\Sponsorship;

class EndSubscription
{
    /**
     * Stop billing a seat and take back whatever allowance is left this
     * month. The company still pays for the current month in full (no
     * proration on the way out), which is why the subscription keeps an end
     * date rather than being deleted.
     */
    public function handle(Sponsorship $sponsorship, string $reason): void
    {
        $subscription = $sponsorship->currentSubscription;

        if (! $subscription) {
            return;
        }

        $remaining = $sponsorship->credits()->remaining();

        $subscription->forceFill(['ended_at' => now()])->save();

        if ($remaining > 0) {
            $sponsorship->user->creditTransactions()->create([
                'subscription_id' => $subscription->id,
                'type' => CreditTransactionType::Expiry,
                'amount' => -$remaining,
                'occurred_at' => now(),
                'note' => $reason,
            ]);
        }
    }
}
