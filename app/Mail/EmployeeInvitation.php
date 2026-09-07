<?php

namespace App\Mail;

use App\Models\Sponsorship;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The email an employee gets when a company reserves a seat for them.
 * The link inside is the only way to accept, so a resend (which rotates
 * the token) makes the previous email's link stop working.
 */
class EmployeeInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Sponsorship $sponsorship)
    {
        $this->sponsorship->loadMissing(['company', 'plan', 'invitedBy']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->sponsorship->company->name} has invited you to TWC",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.employee-invitation',
            with: [
                'company' => $this->sponsorship->company->name,
                'plan' => $this->sponsorship->plan,
                'invitedBy' => $this->sponsorship->invitedBy?->name,
                'url' => route('invite.show', ['sponsorship' => $this->sponsorship->invite_token]),
            ],
        );
    }
}
