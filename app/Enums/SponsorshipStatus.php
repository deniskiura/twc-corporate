<?php

namespace App\Enums;

enum SponsorshipStatus: string
{
    /** Invited by email, has not logged in yet. Costs the company nothing. */
    case Invited = 'invited';

    /** Accepted the invite. The subscription is live and billable. */
    case Joined = 'joined';

    /** Paused by the admin. Not billed after this month; can be resumed. */
    case Suspended = 'suspended';

    /** Withdrawn by the admin before it was accepted. */
    case Revoked = 'revoked';

    /** Taken off the plan by the admin after joining. Can be invited again. */
    case Removed = 'removed';
}
