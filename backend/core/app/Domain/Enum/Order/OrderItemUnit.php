<?php
declare(strict_types=1);

namespace App\Domain\Enum\Order;

enum OrderItemUnit: string
{
    case PIECE = 'PIECE';
    case KILOGRAM = 'KILOGRAM';
}
