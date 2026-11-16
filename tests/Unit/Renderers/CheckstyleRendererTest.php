<?php

namespace Tests\Unit\Renderers;

use PHPUnit\Framework\Attributes\Test;
use App\Renderers\CheckstyleRenderer;
use Symfony\Component\Console\Output\BufferedOutput;

class CheckstyleRendererTest extends RendererTestCase
{
    #[Test]
    public function it_renders_the_exceeded_lines_as_checkstyle(): void
    {
        $output = new BufferedOutput();

        (new CheckstyleRenderer($output))->display($this->makeReport(top: 1));

        $expected = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <checkstyle>
          <file name="a.php">
            <error line="7" severity="error" message="Line exceeds 30 characters; contains 70 characters" source="LineLength"/>
          </file>
          <file name="b&amp;c.php">
            <error line="1" severity="error" message="Line exceeds 30 characters; contains 40 characters" source="LineLength"/>
          </file>
        </checkstyle>

        XML;

        $this->assertSame($expected, $output->fetch());
    }
}
