<?php

namespace Tests\Unit\Renderers;

use PHPUnit\Framework\Attributes\Test;
use App\Renderers\GithubRenderer;
use Symfony\Component\Console\Output\BufferedOutput;

class GithubRendererTest extends RendererTestCase
{
    #[Test]
    public function it_renders_the_exceeded_lines_as_github_annotations(): void
    {
        $output = new BufferedOutput();

        (new GithubRenderer($output))->display($this->makeReport());

        $this->assertSame(
            '::error file=a.php,line=7::Line exceeds 30 characters; contains 70 characters' . PHP_EOL
            . '::error file=b&c.php,line=1::Line exceeds 30 characters; contains 40 characters' . PHP_EOL,
            $output->fetch(),
        );
    }
}
