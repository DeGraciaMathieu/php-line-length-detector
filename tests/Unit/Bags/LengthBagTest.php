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

        $this->assertEqualsWithDelta(28.33, $lengthBag->getAverage(), 0.01);
    }

    #[Test]
    public function it_returns_zero_when_empty(): void
    {
        $lengthBag = new LengthBag(new Thresholds());

        $this->assertSame(0.0, $lengthBag->getAverage());
        $this->assertSame(0, $lengthBag->getMax());
        $this->assertSame(0, $lengthBag->getMedian());
        $this->assertSame(0.0, $lengthBag->getThresholdPercent(10));
    }

    #[Test]
    public function it_can_calculate_percentiles(): void
    {
        $lengthBag = new LengthBag(new Thresholds());

        foreach (range(1, 100) as $length) {
            $lengthBag->add($length);
        }

        $this->assertSame(50, $lengthBag->getMedian());
        $this->assertSame(90, $lengthBag->getPercentile(90));
        $this->assertSame(95, $lengthBag->getPercentile(95));
        $this->assertSame(100, $lengthBag->getPercentile(100));
    }

    #[Test]
    public function it_can_calculate_the_percent_of_a_threshold(): void
    {
        $lengthBag = new LengthBag(new Thresholds([30]));

        $lengthBag->add(20);
        $lengthBag->add(40);
        $lengthBag->add(50);

        $this->assertEqualsWithDelta(66.67, $lengthBag->getThresholdPercent(30), 0.01);
    }
}
