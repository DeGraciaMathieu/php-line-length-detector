<?php

declare(strict_types=1);

namespace App\Bags;

use App\Dtos\Line;

class ExceededLineBag
{
    public array $items = [];

    public function __construct(
        public int $max,
    ) {}

    public function add(Line $line): void
    {
        if ($line->length > $this->max) {
            $this->items[] = $line;
        }
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function getLongest(?int $top = null): array
    {
        $items = $this->items;

        usort($items, fn (Line $a, Line $b) => $b->length <=> $a->length);

        return array_slice($items, 0, $top);
    }
}
