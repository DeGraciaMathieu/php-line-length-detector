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
    private string $path;

    protected function tearDown(): void
    {
        if (isset($this->path)) {
            unlink($this->path);
        }

        parent::tearDown();
    }

    #[Test]
    public function it_measures_the_lines_with_their_indentation(): void
    {
        $lines = $this->analyse(new FileAnalyzer(), [
            '<?php',
            '',
            'class Sample',
            '{',
            '    public function short(): string',
        ]);

        $this->assertEquals([
            new Line('sample.php', 3, 12, 'class Sample'),
            new Line('sample.php', 5, 35, 'public function short(): string'),
        ], $lines);
    }

    #[Test]
    public function it_can_ignore_the_indentation(): void
    {
        $lines = $this->analyse(new FileAnalyzer(withIndentation: false), [
            '    public function short(): string',
        ]);

        $this->assertSame(31, $lines[0]->length);
    }

    #[Test]
    public function it_expands_tabs_to_the_next_tab_stop(): void
    {
        $line = "\t\$a = 1;\t// x";

        $lines = $this->analyse(new FileAnalyzer(tabWidth: 4, minLength: 0), [$line]);
        $this->assertSame(16, $lines[0]->length);

        $lines = $this->analyse(new FileAnalyzer(tabWidth: 2, minLength: 0), [$line]);
        $this->assertSame(14, $lines[0]->length);
    }

    #[Test]
    public function it_counts_characters_instead_of_bytes(): void
    {
        $lines = $this->analyse(new FileAnalyzer(), ['$a = "éééééééééé";']);

        $this->assertSame(18, $lines[0]->length);
    }

    #[Test]
    public function it_ignores_short_lines(): void
    {
        $analyzer = new FileAnalyzer(minLength: 20);

        $lines = $this->analyse($analyzer, [
            '    $short = 1;',
            '    $longEnough = $a + $b;',
        ]);

        $this->assertCount(1, $lines);
        $this->assertSame(2, $lines[0]->number);
    }

    #[Test]
    public function it_ignores_imports_and_comments(): void
    {
        $lines = $this->analyse(new FileAnalyzer(), [
            'use App\Dtos\Thresholds;',
            '// a single line comment',
            '# a shell style comment',
            '/* a block comment */',
            ' * a docblock line here',
            '#[Attribute(Attribute::TARGET_CLASS)]',
            '$user = new User();',
            '$users = $query->get();',
        ]);

        $this->assertSame([6, 7, 8], array_map(fn (Line $line) => $line->number, $lines));
    }

    #[Test]
    public function it_can_include_comments(): void
    {
        $lines = $this->analyse(new FileAnalyzer(includeComments: true), [
            '// a single line comment',
            'use App\Dtos\Thresholds;',
        ]);

        $this->assertCount(1, $lines);
        $this->assertSame(1, $lines[0]->number);
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

    /**
     * @param list<string> $content
     * @return list<Line>
     */
    private function analyse(FileAnalyzer $analyzer, array $content): array
    {
        $this->path ??= tempnam(sys_get_temp_dir(), 'pld');

        file_put_contents($this->path, implode(PHP_EOL, $content) . PHP_EOL);

        $file = new File(fullPath: $this->path, displayPath: 'sample.php');

        return iterator_to_array($analyzer->getLines($file), false);
    }
}
