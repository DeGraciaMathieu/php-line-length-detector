<?php

declare(strict_types=1);

namespace App\Bags;

use App\Dtos\Thresholds;

class LengthBag
{
    private int $count = 0;
    private int $sum = 0;
    private int $max = 0;

    /**
     * @var array<int, int>
     */
    private array $thresholdCounts;

    public function __construct(
        Thresholds $thresholds,
    ) {
        $this->thresholdCounts = array_fill_keys($thresholds->values, 0);
    }

    public function add(int $length): void
    {
        $this->increaseThresholds($length);

        $this->count++;
        $this->sum += $length;
        $this->max = max($this->max, $length);
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getAverage(): int
    {
        if ($this->count === 0) {
            return 0;
        }

        return intdiv($this->sum, $this->count);
    }

    public function getMax(): int
    {
        return $this->max;
    }

    /**
     * @return array<int, int>
     */
    public function getThresholdCounts(): array
    {
        return $this->thresholdCounts;
    }

    private function increaseThresholds(int $length): void
    {
        foreach (array_keys($this->thresholdCounts) as $threshold) {

            if ($threshold < $length) {
                $this->thresholdCounts[$threshold]++;
            }
        }
    }
}
