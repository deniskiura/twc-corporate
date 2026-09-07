<x-mail::message>
# You're invited to TWC

**{{ $company }}** is sponsoring a **{{ $plan->name }}** plan for you: {{ $plan->monthly_credits }} credits a month to spend on classes and sessions across Nairobi.

Your subscription starts when you accept, not before, so there's nothing to do until you're ready.

<x-mail::button :url="$url">
Accept invite
</x-mail::button>

@if ($invitedBy)
Invited by {{ $invitedBy }}.
@endif
If you weren't expecting this, you can ignore it.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
