<?php

namespace App\Http\Controllers;

use App\Enums\CreditTransactionType;
use App\Http\Resources\PlanResource;
use App\Models\User;
use App\Support\BillingCycle;
use App\Support\CreditSummary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        // Staff and company admins each have a home of their own.
        if ($user->isStaff()) {
            return to_route('admin.overview');
        }

        if ($user->isCompanyAdmin()) {
            return to_route('company.users');
        }

        return Inertia::render('Dashboard', [
            'membership' => $this->membership($user),
        ]);
    }

    /**
     * What a sponsored employee sees: who pays, and how many credits are left.
     *
     * @return array<string, mixed>|null
     */
    private function membership(User $user): ?array
    {
        $sponsorship = $user->sponsorship;

        if (! $sponsorship || ! ($sponsorship->hasJoined() || $sponsorship->isSuspended())) {
            return null;
        }

        $cycle = BillingCycle::current();
        $transactions = $user->creditTransactions()->inCycle($cycle)->get();

        return [
            'status' => $sponsorship->status,
            'company' => $sponsorship->company->name,
            'plan' => PlanResource::make($sponsorship->plan)->resolve(),
            'joined_at' => $sponsorship->joined_at,
            'billing_cycle' => $cycle->toArray(),
            'credits' => [
                ...CreditSummary::fromTransactions($transactions)->toArray(),
                'purchased' => (int) $transactions->where('type', CreditTransactionType::Purchase)->sum('amount'),
            ],
        ];
    }
}
