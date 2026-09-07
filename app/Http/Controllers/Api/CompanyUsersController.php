<?php

namespace App\Http\Controllers\Api;

use App\Actions\Corporate\RemoveEmployee;
use App\Actions\Corporate\ResumeEmployee;
use App\Actions\Corporate\SuspendEmployee;
use App\Http\Controllers\Controller;
use App\Http\Resources\SponsoredUserResource;
use App\Support\CompanyTeam;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CompanyUsersController extends Controller
{
    /**
     * Everyone the admin's company manages, invited, joined or suspended,
     * with this month's credit position. The company always comes from the
     * caller's token; there is no way to ask for a different one.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $team = CompanyTeam::load($request->user()->company);

        return SponsoredUserResource::collection($team->seats)
            ->additional(['meta' => $team->meta()]);
    }

    /**
     * Pause a joined employee's seat.
     */
    public function suspend(Request $request, SuspendEmployee $suspendEmployee, int $sponsorship): SponsoredUserResource
    {
        $seat = $suspendEmployee->handle($request->user()->company->findSeat($sponsorship));

        return new SponsoredUserResource($seat->fresh(['plan', 'user']));
    }

    /**
     * Put a suspended seat back to work.
     */
    public function resume(Request $request, ResumeEmployee $resumeEmployee, int $sponsorship): SponsoredUserResource
    {
        $seat = $resumeEmployee->handle($request->user()->company->findSeat($sponsorship));

        return new SponsoredUserResource($seat->fresh(['plan', 'user']));
    }

    /**
     * Take an employee off the plan for good.
     */
    public function destroy(Request $request, RemoveEmployee $removeEmployee, int $sponsorship): Response
    {
        $removeEmployee->handle($request->user()->company->findSeat($sponsorship));

        return response()->noContent();
    }
}
