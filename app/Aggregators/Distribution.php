<?php

declare(strict_types=1);

namespace App\Aggregators;

class Distribution
{
    private array $steps;

    /**
     * @param array<int, string> $steps
     */
    public function __construct(array $steps)
    {
        $this->steps = $this->fillSteps($steps);
    }

    public function add(int $lenght): void
    {
        foreach ($this->steps as $step => $_) {

            if ($lenght > $step) {
                $this->steps[$step]++;
                break;
            }
        }
    }

    public function steps(): array
    {
        return $this->steps;
    }

    private function fillSteps(array $steps): array
    {
        $steps = array_fill_keys($steps, 0);

        $steps = array_reverse($steps, true); 

        return $steps;
    }
}   
