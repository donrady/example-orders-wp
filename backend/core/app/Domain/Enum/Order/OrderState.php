<?php
declare(strict_types=1);

namespace App\Domain\Enum\Order;

use ElityDEV\StateMachine\Domain\Enum\State;

enum OrderState: string implements State
{
    case NEW = 'NEW';
    case WAITING_FOR_CONFIRMATION = 'WAITING_FOR_CONFIRMATION';
    case CONFIRMED = 'CONFIRMED';
    case COMPLETED = 'COMPLETED';
    case REJECTED = 'REJECTED';
    case CANCELED_BY_CUSTOMER = 'CANCELED_BY_CUSTOMER';
    case FAILED = 'FAILED';
    case EXPIRED = 'EXPIRED';
}
