<?php

namespace App\Models;

use App\Enums\CreditTransactionType;
use App\Enums\SponsorshipStatus;
use App\Support\BillingCycle;
use App\Support\CreditSummary;
use Carbon\CarbonImmutable;
use Closure;
use Database\Factories\SponsorshipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * One invited seat on a company's plan.
 *
 * It starts life as an invite and becomes a managed user when the employee
 * accepts. The subscription, and therefore the bill, only starts then.
 *
 * @property int $id
 * @property int $company_id
 * @property int $plan_id
 * @property int|null $invited_by_id
 * @property int|null $user_id
 * @property string $email
 * @property string $invite_token
 * @property SponsorshipStatus $status
 * @property CarbonImmutable $invited_at
 * @property CarbonImmutable $last_sent_at
 * @property CarbonImmutable|null $joined_at
 * @property CarbonImmutable|null $revoked_at
 * @property int|string|null $cycle_allowance
 * @property int|string|null $cycle_spend
 * @property-read Company $company
 * @property-read Plan $plan
 * @property-read User|null $user
 * @property-read Subscription|null $currentSubscription
 */
#[Fillable(['plan_id', 'invited_by_id', 'email', 'invite_token', 'status', 'invited_at', 'last_sent_at'])]
#[Hidden(['invite_token'])]
class Sponsorship extends Model
{
    /** @use HasFactory<SponsorshipFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SponsorshipStatus::class,
            'invited_at' => 'immutable_datetime',
            'last_sent_at' => 'immutable_datetime',
            'joined_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
        ];
    }

    public static function generateToken(): string
    {
        return Str::random(48);
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_id');
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * The subscription currently being billed, if the seat is live.
     *
     * @return HasOne<Subscription, $this>
     */
    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->whereNull('ended_at')->latestOfMany('started_at');
    }

    /**
     * The employee's credit ledger. Keyed on user_id rather than the primary
     * key so it can be summed straight from the team list query.
     *
     * @return HasMany<CreditTransaction, $this>
     */
    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class, 'user_id', 'user_id');
    }

    public function isPending(): bool
    {
        return $this->status === SponsorshipStatus::Invited;
    }

    public function hasJoined(): bool
    {
        return $this->status === SponsorshipStatus::Joined;
    }

    /**
     * How long the invite has gone unanswered since it was last sent.
     */
    public function daysPending(): ?int
    {
        if (! $this->isPending()) {
            return null;
        }

        return (int) $this->last_sent_at->diffInDays(now());
    }

    /**
     * An invite the admin should probably chase or withdraw.
     */
    public function isStale(): bool
    {
        return $this->isPending()
            && $this->daysPending() >= config('corporate.stale_invite_days');
    }

    /**
     * The credits the company gave this cycle and what has been booked
     * against them. Top-ups the employee paid for themselves are their own
     * business and are deliberately left out of the admin's view.
     */
    public function credits(): CreditSummary
    {
        if (! $this->hasJoined()) {
            return CreditSummary::unused($this->plan->monthly_credits);
        }

        if (! array_key_exists('cycle_allowance', $this->attributes)) {
            $this->loadSum(self::cycleCreditAggregates(BillingCycle::current()), 'amount');
        }

        // Spends are stored as negative amounts, so their sum is flipped.
        return new CreditSummary(
            allowance: (int) $this->cycle_allowance,
            used: abs((int) $this->cycle_spend),
        );
    }

    /**
     * Sum the cycle's allowance and spend in the same query as the list,
     * so the team page never runs one query per employee.
     *
     * @param  Builder<Sponsorship>  $query
     */
    #[Scope]
    protected function withCycleCredits(Builder $query, BillingCycle $cycle): void
    {
        $query->withSum(self::cycleCreditAggregates($cycle), 'amount');
    }

    /**
     * @return array<string, Closure(Builder<CreditTransaction>): Builder<CreditTransaction>>
     */
    private static function cycleCreditAggregates(BillingCycle $cycle): array
    {
        return [
            'creditTransactions as cycle_allowance' => fn (Builder $query) => self::ofTypeInCycle($query, CreditTransactionType::Allowance, $cycle),
            'creditTransactions as cycle_spend' => fn (Builder $query) => self::ofTypeInCycle($query, CreditTransactionType::Spend, $cycle),
        ];
    }

    /**
     * @param  Builder<CreditTransaction>  $query
     * @return Builder<CreditTransaction>
     */
    private static function ofTypeInCycle(Builder $query, CreditTransactionType $type, BillingCycle $cycle): Builder
    {
        return $query->where('type', $type)->inCycle($cycle);
    }
}
