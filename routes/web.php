<?php

use App\Http\Controllers\Company\UsersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JoinCompanyController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

// Invite links land here. No login needed: the token in the URL is the proof.
Route::get('invite/{sponsorship:invite_token}', [JoinCompanyController::class, 'show'])->name('invite.show');
Route::post('invite/{sponsorship:invite_token}', [JoinCompanyController::class, 'store'])->name('invite.accept');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('company/users', [UsersController::class, 'index'])
        ->middleware('company-admin')
        ->name('company.users');
});

require __DIR__.'/settings.php';
