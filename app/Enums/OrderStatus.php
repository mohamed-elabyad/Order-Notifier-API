<?php

namespace App\Enums;

use App\Traits\EnumFeatures;

enum OrderStatus: string
{
    use EnumFeatures;

    case PLACED = 'placed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
}
