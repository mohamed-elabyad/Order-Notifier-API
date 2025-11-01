<?php

namespace App\Traits;

trait EnumFeatures
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
