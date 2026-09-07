<?php

namespace App\Enums;

enum CreditTransactionType: string
{
    /** Monthly credits granted by the company's plan. */
    case Allowance = 'allowance';

    /** Extra credits the employee bought for themselves. */
    case Purchase = 'purchase';

    /** A class or session booked with credits. */
    case Spend = 'spend';

    /** Unused allowance removed at the end of the billing cycle. */
    case Expiry = 'expiry';

    /** A manual correction by TWC staff. */
    case Adjustment = 'adjustment';
}
