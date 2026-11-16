<?php

namespace Tests\Unit\Analyzers;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Dtos\Line;
use App\Analyzers\FileAnalyzer;
use DeGraciaMathieu\FileExplorer\File;
use RuntimeException;

class FileAnalyzerTest extends TestCase
{
    #[Test]
    public function it_measures_the_lines_of_a_file(): void
    {
        $file = new File(
            fullPath: base_path('tests/Fixtures/Sample.php'),
            displayPath: 'Sample.php',
        );

        $lines = iterator_to_array((new FileAnalyzer())->getLines($file), false);

        $this->assertEquals([
            new Line('Sample.php', 3, 12),
            new Line('Sample.php', 5, 31),
            new Line('Sample.php', 7, 62),
        ], $lines);
    }

    #[Test]
    public function it_fails_on_an_unreadable_file(): void
    {
        $file = new File(
            fullPath: base_path('tests/Fixtures/missing.php'),
            displayPath: 'missing.php',
        );

        $this->expectException(RuntimeException::class);

        iterator_to_array((new FileAnalyzer())->getLines($file));
    }
}
