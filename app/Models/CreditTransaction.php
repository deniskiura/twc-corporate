<?php

namespace App\Models;

use App\Enums\CreditTransactionType;
use App\Support\BillingCycle;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line in a user's credit ledger. Positive amounts add credits and
 * negative amounts spend them.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $subscription_id
 * @property CreditTransactionType $type
 * @property int $amount
 * @property CarbonImmutable $occurred_at
 * @property string|null $note
 */
#[Fillable(['subscription_id', 'type', 'amount', 'occurred_at', 'note'])]
class CreditTransaction extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CreditTransactionType::class,
            'occurred_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * @param  Builder<CreditTransaction>  $query
     */
    #[Scope]
    protected function inCycle(Builder $query, BillingCycle $cycle): void
    {
        $query->whereBetween('occurred_at', [$cycle->start, $cycle->end]);
    }
}
