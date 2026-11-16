<?php

namespace Tests\Unit\Configs;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Configs\ConfigFile;
use InvalidArgumentException;

class ConfigFileTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = sys_get_temp_dir() . '/config-' . uniqid() . '.json';
    }

    protected function tearDown(): void
    {
        @unlink($this->path);

        parent::tearDown();
    }

    #[Test]
    public function it_normalizes_the_options(): void
    {
        file_put_contents($this->path, json_encode([
            'paths' => ['app', 'src'],
            'thresholds' => [120, 80],
            'max' => 120,
            'format' => 'json',
            'by-file' => true,
            'exclude' => ['vendor'],
        ]));

        $this->assertSame([
            'paths' => ['app', 'src'],
            'thresholds' => '120,80',
            'max' => '120',
            'format' => 'json',
            'by-file' => true,
            'exclude' => ['vendor'],
        ], ConfigFile::fromFile($this->path)->options);
    }

    #[Test]
    public function it_rejects_an_unknown_option(): void
    {
        file_put_contents($this->path, '{"foo": 1}');

        $this->expectExceptionMessage('The config option [foo] is unknown or has an invalid value.');

        ConfigFile::fromFile($this->path);
    }

    #[Test]
    public function it_rejects_an_invalid_value(): void
    {
        file_put_contents($this->path, '{"by-file": "yes"}');

        $this->expectException(InvalidArgumentException::class);

        ConfigFile::fromFile($this->path);
    }

    #[Test]
    public function it_rejects_an_invalid_json(): void
    {
        file_put_contents($this->path, '[1');

        $this->expectExceptionMessage("The config file [$this->path] is not a valid JSON object.");

        ConfigFile::fromFile($this->path);
    }
}
