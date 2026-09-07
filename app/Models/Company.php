<?php

namespace App\Models;

use App\Enums\SponsorshipStatus;
use App\Enums\UserRole;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $joined_count
 * @property int|null $invited_count
 * @property int|null $stale_invites_count
 * @property int|string|null $monthly_run_rate
 * @property-read Collection<int, User> $admins
 */
#[Fillable(['name'])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    /**
     * @return HasMany<User, $this>
     */
    public function admins(): HasMany
    {
        return $this->hasMany(User::class)->where('role', UserRole::CompanyAdmin);
    }

    /**
     * Every seat the company has ever invited, including revoked ones.
     *
     * @return HasMany<Sponsorship, $this>
     */
    public function sponsorships(): HasMany
    {
        return $this->hasMany(Sponsorship::class);
    }

    /**
     * The seats an admin manages: invited, joined and suspended. Withdrawn
     * invites and removed employees are history.
     *
     * @return HasMany<Sponsorship, $this>
     */
    public function team(): HasMany
    {
        return $this->sponsorships()->whereNotIn('status', [
            SponsorshipStatus::Revoked,
            SponsorshipStatus::Removed,
        ]);
    }

    /**
     * Look a seat up through the company rather than globally, so another
     * company's seat is a plain 404 rather than a leak.
     */
    public function findSeat(int $id): Sponsorship
    {
        return $this->sponsorships()->findOrFail($id);
    }

    /**
     * @return HasManyThrough<Subscription, Sponsorship, $this>
     */
    public function subscriptions(): HasManyThrough
    {
        return $this->hasManyThrough(Subscription::class, Sponsorship::class);
    }

    /**
     * How many seats are joined, invited, or invited and going stale.
     *
     * @param  Builder<Company>  $query
     */
    #[Scope]
    protected function withSeatCounts(Builder $query): void
    {
        $query->withCount([
            'sponsorships as joined_count' => fn (Builder $query) => $query->where('status', SponsorshipStatus::Joined),
            'sponsorships as invited_count' => fn (Builder $query) => $query->where('status', SponsorshipStatus::Invited),
            'sponsorships as stale_invites_count' => fn (Builder $query) => self::staleSeats($query),
        ]);
    }

    /**
     * @param  Builder<Sponsorship>  $query
     * @return Builder<Sponsorship>
     */
    private static function staleSeats(Builder $query): Builder
    {
        return $query->stale();
    }

    /**
     * List price of the company's active seats per month, before proration.
     * A run-rate for TWC staff, not an invoice.
     *
     * @param  Builder<Company>  $query
     */
    #[Scope]
    protected function withMonthlyRunRate(Builder $query): void
    {
        $query->addSelect([
            'monthly_run_rate' => Subscription::query()
                ->selectRaw('coalesce(sum(plans.monthly_price), 0)')
                ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
                ->join('sponsorships', 'sponsorships.id', '=', 'subscriptions.sponsorship_id')
                ->whereColumn('sponsorships.company_id', 'companies.id')
                ->whereNull('subscriptions.ended_at'),
        ]);
    }
}
