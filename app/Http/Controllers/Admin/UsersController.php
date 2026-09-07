<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    /**
     * Every account on the platform, searchable by name or email.
     */
    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->value());

        $users = User::query()
            ->with('company')
            ->when($search !== '', fn (Builder $query) => $query->where(
                fn (Builder $query) => $query
                    ->whereLike('name', "%{$search}%")
                    ->orWhereLike('email', "%{$search}%"),
            ))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (User $user) => UserResource::make($user)->resolve());

        return Inertia::render('admin/Users', [
            'users' => $users,
            'search' => $search,
        ]);
    }
}
