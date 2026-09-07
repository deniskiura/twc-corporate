<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SponsoredUserResource;
use App\Support\CompanyTeam;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompanyUsersController extends Controller
{
    /**
     * Everyone the admin's company manages, invited or joined, with this
     * month's credit position. The company always comes from the caller's
     * token; there is no way to ask for a different one.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $team = CompanyTeam::load($request->user()->company);

        return SponsoredUserResource::collection($team->seats)
            ->additional(['meta' => $team->meta()]);
    }
}
