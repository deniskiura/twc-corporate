<?php

namespace App\Http\Controllers;

use App\Actions\Corporate\AcceptInvitation;
use App\Http\Requests\JoinCompanyRequest;
use App\Http\Resources\PlanResource;
use App\Models\Sponsorship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class JoinCompanyController extends Controller
{
    /**
     * Where an invite link lands. Shows what the employee is being offered,
     * or why the link no longer works.
     */
    public function show(Sponsorship $sponsorship): Response
    {
        return Inertia::render('invite/Accept', [
            'token' => $sponsorship->invite_token,
            'status' => $sponsorship->status,
            'email' => $sponsorship->email,
            'company' => $sponsorship->company->name,
            'plan' => PlanResource::make($sponsorship->plan)->resolve(),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    /**
     * Accept the invite from the browser and log the new employee in.
     */
    public function store(
        JoinCompanyRequest $request,
        AcceptInvitation $acceptInvitation,
        Sponsorship $sponsorship,
    ): RedirectResponse {
        $user = $acceptInvitation->handle(
            $sponsorship,
            name: $request->string('name')->value(),
            password: $request->string('password')->value(),
        );

        Auth::login($user);
        $request->session()->regenerate();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "You're in. {$sponsorship->company->name} sponsors your plan from today.",
        ]);

        return to_route('dashboard');
    }
}
