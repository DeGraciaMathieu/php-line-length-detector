<?php

namespace Tests\Unit\Baselines;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Dtos\Line;
use App\Baselines\Baseline;
use InvalidArgumentException;

class BaselineTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = sys_get_temp_dir() . '/baseline-' . uniqid() . '.json';
    }

    protected function tearDown(): void
    {
        @unlink($this->path);

        parent::tearDown();
    }

    #[Test]
    public function it_recognizes_a_known_line_even_if_it_moved(): void
    {
        $baseline = Baseline::fromLines([new Line('a.php', 3, 90, '    $a = 1;')]);

        $this->assertTrue($baseline->consume(new Line('a.php', 12, 90, '$a = 1;')));
    }

    #[Test]
    public function it_does_not_recognize_a_line_of_another_file(): void
    {
        $baseline = Baseline::fromLines([new Line('a.php', 3, 90, '$a = 1;')]);

        $this->assertFalse($baseline->consume(new Line('b.php', 3, 90, '$a = 1;')));
    }

    #[Test]
    public function it_uses_up_the_occurrences(): void
    {
        $baseline = Baseline::fromLines([new Line('a.php', 3, 90, '$a = 1;')]);

        $this->assertTrue($baseline->consume(new Line('a.php', 3, 90, '$a = 1;')));
        $this->assertFalse($baseline->consume(new Line('a.php', 4, 90, '$a = 1;')));
    }

    #[Test]
    public function it_can_be_saved_and_loaded(): void
    {
        Baseline::fromLines([new Line('a.php', 3, 90, '$a = 1;')])->save($this->path);

        $baseline = Baseline::fromFile($this->path);

        $this->assertTrue($baseline->consume(new Line('a.php', 3, 90, '$a = 1;')));
    }

    #[Test]
    public function it_rejects_an_invalid_file(): void
    {
        file_put_contents($this->path, '{"foo": 1}');

        $this->expectException(InvalidArgumentException::class);

        Baseline::fromFile($this->path);
    }

    #[Test]
    public function it_rejects_a_missing_file(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Baseline::fromFile($this->path);
    }
}
