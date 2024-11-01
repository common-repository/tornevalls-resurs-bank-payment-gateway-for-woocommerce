<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Enum;

/**
 * Defines the possible actions for a payment.
 */
enum ActionType: string
{
    case CREATE = 'CREATE';
    case MODIFY_ORDER = 'MODIFY_ORDER';
    case CAPTURE = 'CAPTURE';
    case REFUND = 'REFUND';
    case CANCEL = 'CANCEL';
}
