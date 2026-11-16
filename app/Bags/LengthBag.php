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
     * Number of lines by length.
     *
     * @var array<int, int>
     */
    private array $histogram = [];

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
        $this->histogram[$length] = ($this->histogram[$length] ?? 0) + 1;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getAverage(): float
    {
        if ($this->count === 0) {
            return 0;
        }

        return $this->sum / $this->count;
    }

    public function getMedian(): int
    {
        return $this->getPercentile(50);
    }

    /**
     * Nearest-rank method: the smallest length that covers $percentile % of the lines.
     */
    public function getPercentile(int $percentile): int
    {
        $rank = (int) ceil($percentile / 100 * $this->count);

        ksort($this->histogram);

        $covered = 0;

        foreach ($this->histogram as $length => $count) {
            $covered += $count;

            if ($covered >= $rank) {
                return $length;
            }
        }

        return 0;
    }

    public function getThresholdPercent(int $threshold): float
    {
        if ($this->count === 0) {
            return 0;
        }

        return ($this->thresholdCounts[$threshold] ?? 0) * 100 / $this->count;
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
