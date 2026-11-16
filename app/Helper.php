<?php

declare(strict_types=1);

namespace App;

class Helper
{
    public static function numberFormat(float $value, int $decimals = 1): string
    {
        return number_format($value, $decimals);
    }
}
