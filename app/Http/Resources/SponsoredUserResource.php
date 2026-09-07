<?php

namespace App\Http\Resources;

use App\Models\Sponsorship;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A seat as the company admin sees it: who it is, whether they've joined,
 * and how they're using this month's credits.
 *
 * @mixin Sponsorship
 */
class SponsoredUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->user?->name,
            'status' => $this->status,
            'plan' => new PlanResource($this->plan),
            'invited_at' => $this->invited_at,
            'last_sent_at' => $this->last_sent_at,
            'joined_at' => $this->joined_at,
            'days_pending' => $this->daysPending(),
            'is_stale' => $this->isStale(),
            'credits' => $this->credits()->toArray(),
        ];
    }
}
