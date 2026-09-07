<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\CreateCompany;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\SponsoredUserResource;
use App\Http\Resources\SubscriptionResource;
use App\Models\Company;
use App\Support\CompanyTeam;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompaniesController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Companies', [
            'companies' => CompanyResource::collection(
                $this->summaries()->orderBy('name')->get(),
            )->resolve(),
        ]);
    }

    /**
     * One company in full: its admins, its team exactly as the company admin
     * sees it, and every subscription it has ever had.
     */
    public function show(int $company): Response
    {
        $company = $this->summaries()->findOrFail($company);
        $team = CompanyTeam::load($company);

        $subscriptions = $company->subscriptions()
            ->with(['plan', 'sponsorship.company', 'sponsorship.user'])
            ->latest('started_at')
            ->get();

        return Inertia::render('admin/Company', [
            'company' => CompanyResource::make($company)->resolve(),
            'seats' => SponsoredUserResource::collection($team->seats)->resolve(),
            'totals' => $team->totals(),
            'billingCycle' => $team->cycle->toArray(),
            'subscriptions' => SubscriptionResource::collection($subscriptions)->resolve(),
        ]);
    }

    public function store(StoreCompanyRequest $request, CreateCompany $createCompany): RedirectResponse
    {
        $company = $createCompany->handle(
            name: $request->string('name')->value(),
            adminName: $request->string('admin_name')->value(),
            adminEmail: $request->string('admin_email')->value(),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "{$company->name} is set up. {$request->string('admin_email')} has been emailed a link to set their password.",
        ]);

        return to_route('admin.companies.show', ['company' => $company->id]);
    }

    /**
     * @return Builder<Company>
     */
    private function summaries(): Builder
    {
        return Company::query()
            ->with('admins')
            ->withSeatCounts()
            ->withMonthlyRunRate();
    }
}
