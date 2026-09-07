<?php

namespace App\Enums;

enum UserRole: string
{
    /** A regular TWC member who pays for themselves. */
    case Member = 'member';

    /** Manages a company's team and sees its billing. */
    case CompanyAdmin = 'company_admin';

    /** A sponsored user whose plan is paid for by their company. */
    case Employee = 'employee';

    /** TWC's own staff. Sees every company, user and subscription. */
    case Staff = 'staff';
}
