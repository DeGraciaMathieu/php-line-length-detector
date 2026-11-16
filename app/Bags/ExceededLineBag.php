<?php

declare(strict_types=1);

namespace App\Bags;

use App\Dtos\Line;
use App\Baselines\Baseline;

class ExceededLineBag
{
    /**
     * @var list<Line>
     */
    private array $items = [];

    public function __construct(
        private readonly int $max,
        private readonly ?Baseline $baseline = null,
    ) {}

    /**
     * Returns whether the line is kept as exceeded, lines known by the baseline are not.
     */
    public function add(Line $line): bool
    {
        if ($line->length <= $this->max) {
            return false;
        }

        if ($this->baseline?->consume($line)) {
            return false;
        }

        $this->items[] = $line;

        return true;
    }

    public function getMax(): int
    {
        return $this->max;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    /**
     * @return list<Line>
     */
    public function getLongest(?int $top = null): array
    {
        $items = $this->items;

        usort($items, fn (Line $a, Line $b) => $b->length <=> $a->length);

        return array_slice($items, 0, $top);
    }
}
