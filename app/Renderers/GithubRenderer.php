<?php

declare(strict_types=1);

namespace App\Renderers;

use App\Dtos\Report;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Workflow commands that GitHub Actions turns into annotations on the pull request.
 */
class GithubRenderer implements Renderer
{
    public function __construct(
        private readonly OutputInterface $output,
    ) {}

    public function display(Report $report): void
    {
        $max = $report->exceededLineBag?->getMax();

        foreach ($report->exceededLineBag?->getLongest() ?? [] as $line) {
            $this->output->writeln(
                "::error file=$line->file,line=$line->number::Line exceeds $max characters; contains $line->length characters",
                OutputInterface::OUTPUT_RAW,
            );
        }
    }
}
