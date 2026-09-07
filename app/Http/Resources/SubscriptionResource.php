<?php

namespace App\Http\Resources;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A billable seat as TWC staff see it: who, which company pays, on what plan,
 * and whether it is still running.
 *
 * @mixin Subscription
 */
class SubscriptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee' => [
                'name' => $this->sponsorship->user?->name,
                'email' => $this->sponsorship->email,
            ],
            'company' => [
                'id' => $this->sponsorship->company->id,
                'name' => $this->sponsorship->company->name,
            ],
            'plan' => PlanResource::make($this->plan)->resolve(),
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'is_active' => $this->ended_at === null,
        ];
    }
}
