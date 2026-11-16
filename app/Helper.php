<?php

declare(strict_types=1);

namespace App;

class Helper
{
    public static function numberFormat(int $value): string
    {
        return number_format($value, 0, ',', ' ');
    }
}
