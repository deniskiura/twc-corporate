<?php

namespace App\Notifications;

use App\Models\Company;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to a company's first admin when TWC sets the company up. It rides on
 * the standard password reset flow, so the admin chooses their own password
 * and nobody at TWC ever handles it.
 */
class CompanyAdminWelcome extends ResetPassword
{
    public function __construct(string $token, private readonly Company $company)
    {
        parent::__construct($token);
    }

    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject("You're the admin for {$this->company->name} on TWC")
            ->line("TWC has set up {$this->company->name}'s corporate wellness account and made you its admin.")
            ->line('Choose a password to get in, then invite your team. Seats only start billing once an employee logs in.')
            ->action('Set my password', $url)
            ->line('If you were not expecting this, you can ignore it.');
    }
}
