<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    /**
     * The admin's team screen. It loads its data from the JSON API like any
     * other client would, so the page only needs the plans and a token.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('company/Users', [
            'company' => $request->user()->company->only(['id', 'name']),
            'plans' => PlanResource::collection(Plan::active()->orderBy('monthly_price')->get())->resolve(),
            'apiToken' => $request->user()->api_token,
        ]);
    }
}
