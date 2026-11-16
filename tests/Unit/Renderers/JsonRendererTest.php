<?php

namespace Tests\Unit\Renderers;

use PHPUnit\Framework\Attributes\Test;
use App\Renderers\JsonRenderer;
use Symfony\Component\Console\Output\BufferedOutput;

class JsonRendererTest extends RendererTestCase
{
    #[Test]
    public function it_renders_the_report_as_json(): void
    {
        $output = new BufferedOutput();

        (new JsonRenderer($output))->display($this->makeReport(byFile: true, top: 1));

        $this->assertSame([
            'summary' => ['lines' => 3, 'largest' => 70, 'average' => 43.3, 'median' => 40, 'p90' => 70, 'p95' => 70],
            'thresholds' => [
                ['length' => 60, 'occurrences' => 1, 'percent' => 33.3],
                ['length' => 30, 'occurrences' => 2, 'percent' => 66.7],
            ],
            'exceeded_lines' => [
                ['file' => 'a.php', 'line' => 7, 'length' => 70],
            ],
            'files' => [
                ['file' => 'a.php', 'lines' => 2, 'largest' => 70, 'average' => 45, 'exceeded' => 1],
            ],
        ], json_decode($output->fetch(), true));
    }

    #[Test]
    public function it_omits_the_sections_that_were_not_requested(): void
    {
        $output = new BufferedOutput();

        (new JsonRenderer($output))->display($this->makeReport());

        $this->assertArrayNotHasKey('files', json_decode($output->fetch(), true));
    }
}
