<?php

declare(strict_types=1);

namespace App\Renderers;

use App\Dtos\Line;
use App\Dtos\Report;
use App\Dtos\FileSummary;
use Symfony\Component\Console\Output\OutputInterface;

class JsonRenderer implements Renderer
{
    public function __construct(
        private readonly OutputInterface $output,
    ) {}

    public function display(Report $report): void
    {
        $lengthBag = $report->lengthBag;

        $data = [
            'summary' => [
                'lines' => $lengthBag->getCount(),
                'largest' => $lengthBag->getMax(),
                'average' => round($lengthBag->getAverage(), 1),
                'median' => $lengthBag->getMedian(),
                'p90' => $lengthBag->getPercentile(90),
                'p95' => $lengthBag->getPercentile(95),
            ],
            'thresholds' => array_map(
                fn (int $threshold, int $count) => [
                    'length' => $threshold,
                    'occurrences' => $count,
                    'percent' => round($lengthBag->getThresholdPercent($threshold), 1),
                ],
                array_keys($lengthBag->getThresholdCounts()),
                $lengthBag->getThresholdCounts(),
            ),
        ];

        if ($report->exceededLineBag) {
            $data['exceeded_lines'] = array_map(
                fn (Line $line) => [
                    'file' => $line->file,
                    'line' => $line->number,
                    'length' => $line->length,
                ],
                $report->exceededLineBag->getLongest($report->top),
            );
        }

        if ($report->fileBag) {
            $data['files'] = array_map(
                fn (FileSummary $summary) => [
                    'file' => $summary->file,
                    'lines' => $summary->count,
                    'largest' => $summary->max,
                    'average' => round($summary->average, 1),
                    'exceeded' => $summary->exceeded,
                ],
                $report->fileBag->getSummaries($report->top),
            );
        }

        $this->output->writeln(
            (string) json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            OutputInterface::OUTPUT_RAW,
        );
    }
}
