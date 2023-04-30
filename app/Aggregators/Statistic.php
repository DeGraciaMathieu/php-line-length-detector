<?php

declare(strict_types=1);

namespace App\Aggregators;

class Statistic 
{
    private int $sum = 0;
    private int $count = 0;
    private int $max = 0;

    public function add(int $lenght): void
    {
        $this->aggregateSum($lenght);
        $this->increaseCount();
        $this->setMaxValue($lenght);
    }

    public function average(): int
    {
        return (int) ($this->sum / $this->count);
    }

    public function max(): int
    {
        return $this->max;
    }

    private function aggregateSum(int $lenght): void
    {
        $this->sum += $lenght;
    }

    private function increaseCount(): void
    {
        $this->count++;
    }

    private function setMaxValue(int $lenght): void
    {
        if ($this->max < $lenght) {
            $this->max = $lenght;
        }
    }
}   
