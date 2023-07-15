<?php

declare(strict_types=1);

namespace App\Dtos;

class Thresholds
{
    public function __construct(
        public array $values = [],
    ) {}

    public static function fromString(string $thresholds): self
    {
        $thresholds = explode(',', $thresholds);

        return new self(
            values: array_fill_keys($thresholds, 0),
        );
    }
}
