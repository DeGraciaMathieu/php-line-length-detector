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
            ->expectsOutputToContain('| 3           | 70           | 39.0         | 35     | 70  | 70  |')
            ->assertSuccessful();
    }

    #[Test]
    public function it_displays_the_thresholds(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --thresholds=60,30')
            ->expectsOutputToContain(
                '| > 60   | 1          | 33.3 %  |' . PHP_EOL
                . '| > 30   | 2          | 66.7 %  |'
            )
            ->doesntExpectOutputToContain('> 80')
            ->assertSuccessful();
    }

    #[Test]
    public function it_succeeds_when_no_line_exceeds_the_max(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --max=70')
            ->doesntExpectOutputToContain('Sample.php')
            ->assertSuccessful();
    }

    #[Test]
    public function it_fails_when_a_line_exceeds_the_max(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --max=69')
            ->expectsOutputToContain('| tests/Fixtures/Sample.php | 7    | 70     |')
            ->assertFailed();
    }

    #[Test]
    public function it_limits_the_exceeded_lines(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --max=10 --top=1')
            ->expectsOutputToContain('| 7    | 70     |')
            ->doesntExpectOutputToContain('| 5    | 35     |')
            ->assertFailed();
    }

    #[Test]
    public function it_warns_when_there_is_no_line_to_analyze(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . '/Empty --max=10')
            ->expectsOutput('No line to analyze.')
            ->assertSuccessful();
    }

    #[Test]
    public function it_rejects_a_missing_path(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ',missing')
            ->expectsOutput('The path [missing] does not exist.')
            ->assertExitCode(2);
    }

    #[Test]
    public function it_rejects_invalid_thresholds(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --thresholds=60,abc')
            ->expectsOutput('The threshold [abc] must be a positive integer.')
            ->assertExitCode(2);
    }

    #[Test]
    public function it_rejects_an_invalid_max(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --max=abc')
            ->expectsOutput('The --max option must be an integer greater than or equal to 1.')
            ->assertExitCode(2);
    }

    #[Test]
    public function it_rejects_an_invalid_top(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --max=10 --top=0')
            ->expectsOutput('The --top option must be an integer greater than or equal to 1.')
            ->assertExitCode(2);
    }

    #[Test]
    public function it_can_ignore_the_indentation(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --without-indentation')
            ->expectsOutputToContain('| 3           | 62           | 35.0         | 31     | 62  | 62  |')
            ->assertSuccessful();
    }

    #[Test]
    public function it_can_exclude_files(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --exclude=Sample\.php$')
            ->expectsOutput('No line to analyze.')
            ->assertSuccessful();
    }

    #[Test]
    public function it_rejects_an_invalid_exclude_pattern(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --exclude=[')
            ->expectsOutput('The exclude pattern [[] is not a valid regular expression.')
            ->assertExitCode(2);
    }

    #[Test]
    public function it_can_display_a_summary_by_file(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --by-file --max=69')
            ->expectsOutputToContain('| tests/Fixtures/Sample.php | 3     | 70           | 39.0         | 1        |')
            ->assertFailed();
    }

    #[Test]
    public function it_can_render_json(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --format=json --max=69')
            ->expectsOutputToContain('"file": "tests/Fixtures/Sample.php"')
            ->doesntExpectOutputToContain('PHP Line Length Detector')
            ->assertFailed();
    }

    #[Test]
    public function it_rejects_an_unsupported_format(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --format=xml')
            ->expectsOutput('The format [xml] is not supported.')
            ->assertExitCode(2);
    }

    #[Test]
    public function it_requires_max_for_the_checkstyle_format(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --format=checkstyle')
            ->expectsOutput('The checkstyle format requires the --max option.')
            ->assertExitCode(2);
    }

    #[Test]
    public function it_ignores_the_lines_of_a_generated_baseline(): void
    {
        $path = sys_get_temp_dir() . '/baseline-' . uniqid() . '.json';

        $this->artisan('inspect ' . self::FIXTURES . " --max=34 --generate-baseline=$path")
            ->expectsOutput("Baseline generated in [$path] with 2 lines.")
            ->assertSuccessful();

        $this->artisan('inspect ' . self::FIXTURES . " --max=34 --baseline=$path")
            ->doesntExpectOutputToContain('Sample.php')
            ->assertSuccessful();

        unlink($path);
    }

    #[Test]
    public function it_requires_max_for_the_baseline(): void
    {
        $this->artisan('inspect ' . self::FIXTURES . ' --baseline=baseline.json')
            ->expectsOutput('The --baseline option requires the --max option.')
            ->assertExitCode(2);
    }

    #[Test]
    public function it_reads_the_options_from_a_config_file(): void
    {
        $path = sys_get_temp_dir() . '/config-' . uniqid() . '.json';

        file_put_contents($path, json_encode(['paths' => [self::FIXTURES], 'max' => 69]));

        $this->artisan("inspect --config=$path")
            ->expectsOutputToContain('| tests/Fixtures/Sample.php | 7    | 70     |')
            ->assertFailed();

        $this->artisan("inspect --config=$path --max=70")
            ->assertSuccessful();

        unlink($path);
    }

    #[Test]
    public function it_requires_a_path(): void
    {
        $this->artisan('inspect')
            ->expectsOutput('No path to analyze, give one or set "paths" in the config file.')
            ->assertExitCode(2);
    }
}
