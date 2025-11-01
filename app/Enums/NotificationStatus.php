<?php

namespace App\Enums;

use App\Traits\EnumFeatures;

enum NotificationStatus: string
{
    use EnumFeatures;

    case SUCCESS = 'success';
    case FAILED = 'failed';
}
