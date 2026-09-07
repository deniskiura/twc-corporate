<?php

namespace App\Http\Requests\Api;

use App\Enums\SponsorshipStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InviteEmployeeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                // One live seat per email per company. Revoked invites don't
                // count, so someone can be re-invited after a mistake.
                Rule::unique('sponsorships', 'email')
                    ->where('company_id', $this->user()->company_id)
                    ->whereNot('status', SponsorshipStatus::Revoked->value),
            ],
            'plan_id' => [
                'required',
                'integer',
                Rule::exists('plans', 'id')->where('is_active', true),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'This person has already been invited or has joined.',
            'plan_id.exists' => 'Choose one of the available plans.',
        ];
    }
}
