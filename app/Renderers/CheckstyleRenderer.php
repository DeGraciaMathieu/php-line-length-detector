<?php

declare(strict_types=1);

namespace App\Renderers;

use App\Dtos\Line;
use App\Dtos\Report;
use Symfony\Component\Console\Output\OutputInterface;

class CheckstyleRenderer implements Renderer
{
    public function __construct(
        private readonly OutputInterface $output,
    ) {}

    public function display(Report $report): void
    {
        $max = $report->exceededLineBag?->getMax();
        $lines = $report->exceededLineBag?->getLongest() ?? [];

        $xml = ['<?xml version="1.0" encoding="UTF-8"?>', '<checkstyle>'];

        foreach ($this->groupByFile($lines) as $file => $fileLines) {

            $xml[] = '  <file name="' . $this->escape($file) . '">';

            foreach ($fileLines as $line) {
                $message = "Line exceeds $max characters; contains $line->length characters";

                $xml[] = '    <error line="' . $line->number . '" severity="error"'
                    . ' message="' . $this->escape($message) . '" source="LineLength"/>';
            }

            $xml[] = '  </file>';
        }

        $xml[] = '</checkstyle>';

        $this->output->writeln($xml, OutputInterface::OUTPUT_RAW);
    }

    /**
     * @param list<Line> $lines
     * @return array<string, list<Line>>
     */
    private function groupByFile(array $lines): array
    {
        usort($lines, fn (Line $a, Line $b) => [$a->file, $a->number] <=> [$b->file, $b->number]);

        $files = [];

        foreach ($lines as $line) {
            $files[$line->file][] = $line;
        }

        return $files;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
