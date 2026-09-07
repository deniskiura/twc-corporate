<?php

use App\Http\Controllers\Api\AcceptInvitationController;
use App\Http\Controllers\Api\CompanyUsersController;
use App\Http\Controllers\Api\InvitationController;
use Illuminate\Support\Facades\Route;

// Anyone holding a valid invite token can accept it. The token is the credential.
Route::post('invites/accept', AcceptInvitationController::class)->name('api.invites.accept');

// Everything below identifies the caller by `Authorization: Bearer <api_token>`
// and is scoped to that admin's own company.
Route::middleware(['auth:api', 'company-admin'])
    ->prefix('company')
    ->name('api.company.')
    ->group(function () {
        Route::get('users', [CompanyUsersController::class, 'index'])->name('users.index');
        Route::post('invites', [InvitationController::class, 'store'])->name('invites.store');
        Route::post('invites/{sponsorship}/resend', [InvitationController::class, 'resend'])->name('invites.resend');
        Route::delete('invites/{sponsorship}', [InvitationController::class, 'destroy'])->name('invites.destroy');
    });
