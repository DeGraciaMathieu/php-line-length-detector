<?php

namespace Tests\Unit\Bags;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Dtos\Line;
use App\Bags\ExceededLineBag;
use App\Baselines\Baseline;

class ExceededLineBagTest extends TestCase
{
    #[Test]
    public function it_keeps_only_lines_exceeding_the_max(): void
    {
        $exceededLineBag = new ExceededLineBag(50);

        $exceededLineBag->add(new Line('a.php', 1, 49));
        $exceededLineBag->add(new Line('a.php', 2, 50));
        $exceededLineBag->add(new Line('a.php', 3, 51));

        $lines = $exceededLineBag->getLongest();

        $this->assertCount(1, $lines);
        $this->assertSame(3, $lines[0]->number);
    }

    #[Test]
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

    #[Test]
    public function it_limits_the_longest_lines(): void
    {
        $exceededLineBag = new ExceededLineBag(10);

        $exceededLineBag->add(new Line('a.php', 1, 20));
        $exceededLineBag->add(new Line('a.php', 2, 40));
        $exceededLineBag->add(new Line('b.php', 1, 30));

        $this->assertCount(2, $exceededLineBag->getLongest(2));
    }

    #[Test]
    public function it_knows_when_it_is_empty(): void
    {
        $exceededLineBag = new ExceededLineBag(10);

        $this->assertTrue($exceededLineBag->isEmpty());

        $exceededLineBag->add(new Line('a.php', 1, 20));

        $this->assertFalse($exceededLineBag->isEmpty());
    }

    #[Test]
    public function it_ignores_the_lines_known_by_the_baseline(): void
    {
        $baseline = Baseline::fromLines([new Line('a.php', 1, 20, '$known = 1;')]);

        $exceededLineBag = new ExceededLineBag(10, $baseline);

        $this->assertFalse($exceededLineBag->add(new Line('a.php', 5, 20, '$known = 1;')));
        $this->assertTrue($exceededLineBag->add(new Line('a.php', 6, 20, '$new = 1;')));
        $this->assertCount(1, $exceededLineBag->getLongest());
    }
}
