<?php

declare(strict_types=1);

namespace App\Bags;

use App\Dtos\Thresholds;

class LengthBag
{
    public int $count = 0;
    public array $items = [];

    public function __construct(
        public Thresholds $thresholds,
    ) {}

    public function add(int $length): void
    {
        $this->increaseThresholds($length);

        $this->items[] = $length;
        $this->count += 1;
    }

    public function getAverage(): int
    {
        return (int) (array_sum($this->items) / $this->count);
    }

    public function getMax(): int
    {
        return max($this->items);
    }

    private function increaseThresholds(int $length): void
    {
        foreach ($this->thresholds->values as $threshold => &$values) {

            if ($threshold < $length) {
                $values++;
            }
        }
    }
}
