<?php

namespace App\Support;

use App\Models\Company;
use App\Models\Sponsorship;
use Illuminate\Support\Collection;

/**
 * A company's managed seats for one billing cycle, with the totals the team
 * screen shows. Shared by the company admin's API and TWC staff's view of
 * the same company, so both always agree on the numbers.
 */
final class CompanyTeam
{
    /**
     * @param  Collection<int, Sponsorship>  $seats
     */
    private function __construct(
        public readonly Company $company,
        public readonly BillingCycle $cycle,
        public readonly Collection $seats,
    ) {}

    public static function load(Company $company, ?BillingCycle $cycle = null): self
    {
        $cycle ??= BillingCycle::current();

        $seats = $company->team()
            ->with(['plan', 'user'])
            ->withCycleCredits($cycle)
            ->orderByDesc('invited_at')
            ->get();

        return new self($company, $cycle, $seats);
    }

    /**
     * @return array<string, mixed>
     */
    public function totals(): array
    {
        $joined = $this->seats->filter->hasJoined();
        $invited = $this->seats->filter->isPending();

        $allowance = (int) $joined->sum(fn (Sponsorship $seat) => $seat->credits()->allowance);
        $used = (int) $joined->sum(fn (Sponsorship $seat) => $seat->credits()->used);

        return [
            'joined' => $joined->count(),
            'invited' => $invited->count(),
            'stale_invites' => $invited->filter->isStale()->count(),
            'credits' => [
                'allowance' => $allowance,
                'used' => $used,
                'exhausted' => $allowance > 0 && $used >= $allowance,
            ],
        ];
    }

    /**
     * The `meta` block that travels with the seats.
     *
     * @return array<string, mixed>
     */
    public function meta(): array
    {
        return [
            'company' => ['id' => $this->company->id, 'name' => $this->company->name],
            'billing_cycle' => $this->cycle->toArray(),
            'totals' => $this->totals(),
        ];
    }
}
