<?php

declare(strict_types=1);

namespace App\Dtos;

use InvalidArgumentException;

class Thresholds
{
    /**
     * @param list<int> $values
     */
    public function __construct(
        public readonly array $values = [],
    ) {}

    public static function fromString(string $thresholds): self
    {
        $thresholds = array_map('trim', explode(',', $thresholds));

        foreach ($thresholds as $threshold) {
            if (! ctype_digit($threshold)) {
                throw new InvalidArgumentException("The threshold [$threshold] must be a positive integer.");
            }
        }

        $thresholds = array_unique(array_map('intval', $thresholds));

        rsort($thresholds);

        return new self(
            values: $thresholds,
        );
    }
}
