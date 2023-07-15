<?php

namespace Tests\Unit\Aggregators;

use Tests\TestCase;
use App\Bags\LengthBag;
use App\Dtos\Thresholds;

class LengthBagTest extends TestCase
{
    /**
     * @test
     */
    public function it_able_to_aggregate_values(): void
    {
        $lengthBag = new LengthBag(
            new Thresholds([
                '10' => 0,
                '30' => 0,
                '50' => 0,
            ]),
        );

        $lengthBag->add(5);
        $lengthBag->add(25);
        $lengthBag->add(55);

        $items = $lengthBag->items;

        $this->assertIsArray($items);
        $this->assertCount(3, $items);
        $this->assertSame(5, $items[0]);

        $this->assertSame([
            '10' => 2,
            '30' => 1,
            '50' => 1,
        ], $lengthBag->thresholds->values);
    }

    /**
     * @test
     */
    public function it_able_to_find_the_max_value(): void
    {
        $lengthBag = app(LengthBag::class);

        $lengthBag->add(10);
        $lengthBag->add(30);
        $lengthBag->add(50);

        $max = $lengthBag->getMax();

        $this->assertSame(50, $max);
    }

    /**
     * @test
     */
    public function it_able_to_calculate_the_average(): void
    {
        $lengthBag = new LengthBag(
            new Thresholds([
                '10' => 0,
                '30' => 0,
                '50' => 0,
            ]),
        );

        $lengthBag->add(5);
        $lengthBag->add(25);
        $lengthBag->add(55);

        $average = $lengthBag->getAverage();

        $this->assertSame(28, $average);
    }
}
