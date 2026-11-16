<?php

namespace Tests\Unit\Bags;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Dtos\Line;
use App\Dtos\FileSummary;
use App\Bags\FileBag;

class FileBagTest extends TestCase
{
    #[Test]
    public function it_summarizes_lines_by_file(): void
    {
        $fileBag = new FileBag(max: 30);

        $fileBag->add(new Line('a.php', 1, 20));
        $fileBag->add(new Line('a.php', 2, 40));
        $fileBag->add(new Line('b.php', 1, 10));

        $this->assertEquals([
            new FileSummary('a.php', count: 2, max: 40, average: 30, exceeded: 1),
            new FileSummary('b.php', count: 1, max: 10, average: 10, exceeded: 0),
        ], $fileBag->getSummaries());
    }

    #[Test]
    public function it_sorts_by_exceeded_lines_then_by_longest_line(): void
    {
        $fileBag = new FileBag(max: 30);

        $fileBag->add(new Line('a.php', 1, 90));
        $fileBag->add(new Line('b.php', 1, 40));
        $fileBag->add(new Line('b.php', 2, 40));
        $fileBag->add(new Line('c.php', 1, 50));
        $fileBag->add(new Line('c.php', 2, 50));

        $files = array_map(
            fn (FileSummary $summary) => $summary->file,
            $fileBag->getSummaries(),
        );

        $this->assertSame(['c.php', 'b.php', 'a.php'], $files);
    }

    #[Test]
    public function it_limits_the_summaries(): void
    {
        $fileBag = new FileBag();

        $fileBag->add(new Line('a.php', 1, 20));
        $fileBag->add(new Line('b.php', 1, 30));

        $this->assertCount(1, $fileBag->getSummaries(1));
        $this->assertFalse($fileBag->hasMax());
    }
}
