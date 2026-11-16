<?php

namespace Tests\Unit\Bags;

use Tests\TestCase;
use App\Dtos\Line;
use App\Bags\ExceededLineBag;

class ExceededLineBagTest extends TestCase
{
    /**
     * @test
     */
    public function it_keeps_only_lines_exceeding_the_max(): void
    {
        $exceededLineBag = new ExceededLineBag(50);

        $exceededLineBag->add(new Line('a.php', 1, 49));
        $exceededLineBag->add(new Line('a.php', 2, 50));
        $exceededLineBag->add(new Line('a.php', 3, 51));

        $this->assertCount(1, $exceededLineBag->items);
        $this->assertSame(3, $exceededLineBag->items[0]->number);
    }

    /**
     * @test
     */
    public function it_returns_the_longest_lines_first(): void
    {
        $exceededLineBag = new ExceededLineBag(10);

        $exceededLineBag->add(new Line('a.php', 1, 20));
        $exceededLineBag->add(new Line('a.php', 2, 40));
        $exceededLineBag->add(new Line('b.php', 1, 30));

        $lengths = array_map(
            fn (Line $line) => $line->length,
            $exceededLineBag->getLongest(),
        );

        $this->assertSame([40, 30, 20], $lengths);
    }

    /**
     * @test
     */
    public function it_limits_the_longest_lines(): void
    {
        $exceededLineBag = new ExceededLineBag(10);

        $exceededLineBag->add(new Line('a.php', 1, 20));
        $exceededLineBag->add(new Line('a.php', 2, 40));
        $exceededLineBag->add(new Line('b.php', 1, 30));

        $this->assertCount(2, $exceededLineBag->getLongest(2));
    }

    /**
     * @test
     */
    public function it_knows_when_it_is_empty(): void
    {
        $exceededLineBag = new ExceededLineBag(10);

        $this->assertTrue($exceededLineBag->isEmpty());

        $exceededLineBag->add(new Line('a.php', 1, 20));

        $this->assertFalse($exceededLineBag->isEmpty());
    }
}
