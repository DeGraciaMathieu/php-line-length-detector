<?php

declare(strict_types=1);

namespace App\Dtos;

class Line
{
    public function __construct(
        public string $file,
        public int $number,
        public int $length,
    ) {}
}
