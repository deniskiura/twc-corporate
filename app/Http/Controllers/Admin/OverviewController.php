<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CreditTransactionType;
use App\Enums\SponsorshipStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Company;
use App\Models\CreditTransaction;
use App\Models\Sponsorship;
use App\Models\Subscription;
use App\Models\User;
use App\Support\BillingCycle;
use Inertia\Inertia;
use Inertia\Response;

class OverviewController extends Controller
{
    /**
     * The numbers TWC staff look at first: how many companies and seats
     * there are, what is pending, and how this month's credits are moving.
     */
    public function __invoke(): Response
    {
        $cycle = BillingCycle::current();

        return Inertia::render('admin/Overview', [
            'stats' => [
                'companies' => Company::count(),
                'users' => User::count(),
                'joined_seats' => Sponsorship::where('status', SponsorshipStatus::Joined)->count(),
                'pending_invites' => Sponsorship::where('status', SponsorshipStatus::Invited)->count(),
                'stale_invites' => Sponsorship::query()->stale()->count(),
                'active_subscriptions' => Subscription::query()->active()->count(),
                'monthly_run_rate' => (int) Subscription::query()
                    ->active()
                    ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
                    ->sum('plans.monthly_price'),
                'credits' => [
                    'allowance' => (int) CreditTransaction::query()
                        ->inCycle($cycle)
                        ->where('type', CreditTransactionType::Allowance)
                        ->sum('amount'),
                    'used' => abs((int) CreditTransaction::query()
                        ->inCycle($cycle)
                        ->where('type', CreditTransactionType::Spend)
                        ->sum('amount')),
                ],
            ],
            'billingCycle' => $cycle->toArray(),
            'recentSubscriptions' => SubscriptionResource::collection(
                Subscription::query()
                    ->with(['plan', 'sponsorship.company', 'sponsorship.user'])
                    ->latest('started_at')
                    ->limit(8)
                    ->get(),
            )->resolve(),
        ]);
    }
}
