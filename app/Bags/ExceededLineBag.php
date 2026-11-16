<?php

declare(strict_types=1);

namespace App\Bags;

use App\Dtos\Line;

class ExceededLineBag
{
    /**
     * @var list<Line>
     */
    private array $items = [];

    public function __construct(
        private readonly int $max,
    ) {}

    public function add(Line $line): void
    {
        if ($line->length > $this->max) {
            $this->items[] = $line;
        }
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
