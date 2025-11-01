<?php

namespace App\Enums;

use App\Traits\EnumFeatures;

enum DevicePlatform : string
{
    use EnumFeatures;

    case ANDROID = "android";
    case IOS = "ios";
    case WEB = "web";
}
