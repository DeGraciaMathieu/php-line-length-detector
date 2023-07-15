<?php

namespace App;

class Helper
{
    public static function numberFormat(int $value): string
    {
        return number_format($value, 0, ',', ' ');
    }
}
