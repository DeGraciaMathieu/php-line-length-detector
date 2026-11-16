<?php

declare(strict_types=1);

namespace App\Dtos;

class FileSummary
{
    public function __construct(
        public readonly string $file,
        public readonly int $count,
        public readonly int $max,
        public readonly int $average,
        public readonly int $exceeded,
    ) {}
}
