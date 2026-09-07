<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A billable period on a plan.
 *
 * It starts when the invite is accepted. A plan change or cancellation closes
 * it and opens a new one, so billing history is never rewritten.
 *
 * @property int $id
 * @property int $sponsorship_id
 * @property int $plan_id
 * @property CarbonImmutable $started_at
 * @property CarbonImmutable|null $ended_at
 * @property-read Sponsorship $sponsorship
 * @property-read Plan $plan
 */
#[Fillable(['plan_id', 'started_at', 'ended_at'])]
class Subscription extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'immutable_datetime',
            'ended_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<Sponsorship, $this>
     */
    public function sponsorship(): BelongsTo
    {
        return $this->belongsTo(Sponsorship::class);
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return HasMany<CreditTransaction, $this>
     */
    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    /**
     * @param  Builder<Subscription>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereNull($query->qualifyColumn('ended_at'));
    }
}
