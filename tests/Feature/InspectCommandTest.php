<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class InspectCommandTest extends TestCase
{
    private const FIXTURES = 'tests/Fixtures';

    #[Test]
    public function it_displays_the_summary(): void
    {
        $this->artisan('inspect ' . self::FIXTURES)
            ->expectsOutputToContain('| 3           | 62           | 35           |')
            ->assertSuccessful();
    }

    #[Test]
    public function it_displays_the_thresholds(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --thresholds=60,30')
            ->expectsOutputToContain(
                '| > 60   | 1          | 33 %    |' . PHP_EOL
                . '| > 30   | 2          | 66 %    |'
            )
            ->doesntExpectOutputToContain('> 80')
            ->assertSuccessful();
    }

    #[Test]
    public function it_succeeds_when_no_line_exceeds_the_max(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --max=62')
            ->doesntExpectOutputToContain('Sample.php')
            ->assertSuccessful();
    }

    #[Test]
    public function it_fails_when_a_line_exceeds_the_max(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --max=61')
            ->expectsOutputToContain('| tests/Fixtures/Sample.php | 7    | 62     |')
            ->assertFailed();
    }

    #[Test]
    public function it_limits_the_exceeded_lines(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --max=10 --top=1')
            ->expectsOutputToContain('| 7    | 62     |')
            ->doesntExpectOutputToContain('| 5    | 31     |')
            ->assertFailed();
    }
}
