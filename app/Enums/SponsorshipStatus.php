<?php

namespace App\Enums;

enum SponsorshipStatus: string
{
    /** Invited by email, has not logged in yet. Costs the company nothing. */
    case Invited = 'invited';

    /** Accepted the invite. The subscription is live and billable. */
    case Joined = 'joined';

    /** Withdrawn by the admin before it was accepted. */
    case Revoked = 'revoked';
}
