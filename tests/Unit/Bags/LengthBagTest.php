<?php

namespace Tests\Unit\Bags;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Bags\LengthBag;
use App\Dtos\Thresholds;

class LengthBagTest extends TestCase
{
    #[Test]
    public function it_can_aggregate_values(): void
    {
        $lengthBag = new LengthBag(
            new Thresholds([50, 30, 10]),
        );

        $lengthBag->add(5);
        $lengthBag->add(25);
        $lengthBag->add(55);

        $this->assertSame(3, $lengthBag->getCount());

        $this->assertSame([
            50 => 1,
            30 => 1,
            10 => 2,
        ], $lengthBag->getThresholdCounts());
    }

    #[Test]
    public function it_can_find_the_max_value(): void
    {
        $lengthBag = new LengthBag(new Thresholds());

        $lengthBag->add(10);
        $lengthBag->add(50);
        $lengthBag->add(30);

        $this->assertSame(50, $lengthBag->getMax());
    }

    #[Test]
    public function it_can_calculate_the_average(): void
    {
        $lengthBag = new LengthBag(new Thresholds());

        $lengthBag->add(5);
        $lengthBag->add(25);
        $lengthBag->add(55);

        $this->assertSame(28, $lengthBag->getAverage());
    }

    #[Test]
    public function it_returns_zero_when_empty(): void
    {
        $lengthBag = new LengthBag(new Thresholds());

        $this->assertSame(0, $lengthBag->getAverage());
        $this->assertSame(0, $lengthBag->getMax());
    }
}
