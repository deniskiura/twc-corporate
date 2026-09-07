<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionsController extends Controller
{
    /**
     * Every billable seat across all companies, newest first, with a filter
     * for the ones still running or already ended.
     */
    public function index(Request $request): Response
    {
        $request->validate([
            'status' => ['nullable', Rule::in(['active', 'ended'])],
        ]);

        $status = $request->string('status')->value();

        $subscriptions = Subscription::query()
            ->with(['plan', 'sponsorship.company', 'sponsorship.user'])
            ->when($status === 'active', fn (Builder $query) => $query->active())
            ->when($status === 'ended', fn (Builder $query) => $query->whereNotNull('ended_at'))
            ->latest('started_at')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (Subscription $subscription) => SubscriptionResource::make($subscription)->resolve());

        return Inertia::render('admin/Subscriptions', [
            'subscriptions' => $subscriptions,
            'status' => $status,
            'counts' => [
                'all' => Subscription::count(),
                'active' => Subscription::query()->active()->count(),
                'ended' => Subscription::whereNotNull('ended_at')->count(),
            ],
        ]);
    }
}
