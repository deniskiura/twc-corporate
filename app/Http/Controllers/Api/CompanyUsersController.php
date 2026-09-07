<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SponsoredUserResource;
use App\Models\Sponsorship;
use App\Support\BillingCycle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

class CompanyUsersController extends Controller
{
    /**
     * Everyone the admin's company manages, invited or joined, with this
     * month's credit position. The company always comes from the caller's
     * token; there is no way to ask for a different one.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $company = $request->user()->company;
        $cycle = BillingCycle::current();

        $team = $company->team()
            ->with(['plan', 'user'])
            ->withCycleCredits($cycle)
            ->orderByDesc('invited_at')
            ->get();

        return SponsoredUserResource::collection($team)->additional([
            'meta' => [
                'company' => ['id' => $company->id, 'name' => $company->name],
                'billing_cycle' => $cycle->toArray(),
                'totals' => $this->totals($team),
            ],
        ]);
    }

    /**
     * @param  Collection<int, Sponsorship>  $team
     * @return array<string, mixed>
     */
    private function totals(Collection $team): array
    {
        $joined = $team->filter->hasJoined();
        $invited = $team->filter->isPending();

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
}
