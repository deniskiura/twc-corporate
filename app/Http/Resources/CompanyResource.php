<?php

namespace App\Http\Resources;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A company as TWC staff see it in a list. Expects the seat counts and
 * run-rate to be loaded with the `withSeatCounts` and `withMonthlyRunRate`
 * scopes, and the admins eager loaded.
 *
 * @mixin Company
 */
class CompanyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at,
            'admins' => $this->admins
                ->map(fn (User $admin) => ['id' => $admin->id, 'name' => $admin->name, 'email' => $admin->email])
                ->values(),
            'joined_count' => (int) $this->joined_count,
            'invited_count' => (int) $this->invited_count,
            'stale_invites_count' => (int) $this->stale_invites_count,
            'monthly_run_rate' => (int) $this->monthly_run_rate,
        ];
    }
}
