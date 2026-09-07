<?php

use App\Http\Controllers\Admin\CompaniesController;
use App\Http\Controllers\Admin\OverviewController;
use App\Http\Controllers\Admin\SubscriptionsController;
use App\Http\Controllers\Admin\UsersController;
use Illuminate\Support\Facades\Route;

// TWC's internal console. Staff only; it shows every company's data.
Route::middleware(['auth', 'verified', 'staff'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', OverviewController::class)->name('overview');

        Route::get('companies', [CompaniesController::class, 'index'])->name('companies.index');
        Route::post('companies', [CompaniesController::class, 'store'])->name('companies.store');
        Route::get('companies/{company}', [CompaniesController::class, 'show'])->name('companies.show');

        Route::get('subscriptions', [SubscriptionsController::class, 'index'])->name('subscriptions.index');
        Route::get('users', [UsersController::class, 'index'])->name('users.index');
    });
