<?php

declare(strict_types=1);

namespace App\Bags;

use App\Dtos\Line;
use App\Dtos\FileSummary;

class FileBag
{
    /**
     * @var array<string, array{count: int, sum: int, max: int, exceeded: int}>
     */
    private array $files = [];

    public function __construct(
        private readonly bool $tracksExceeded = false,
    ) {}

    public function add(Line $line, bool $exceeded = false): void
    {
        $file = $this->files[$line->file] ?? ['count' => 0, 'sum' => 0, 'max' => 0, 'exceeded' => 0];

        $file['count']++;
        $file['sum'] += $line->length;
        $file['max'] = max($file['max'], $line->length);

        if ($exceeded) {
            $file['exceeded']++;
        }

        $this->files[$line->file] = $file;
    }

    public function tracksExceeded(): bool
    {
        return $this->tracksExceeded;
    }

    /**
     * Sorted by exceeded lines, then by longest line.
     *
     * @return list<FileSummary>
     */
    public function getSummaries(?int $top = null): array
    {
        $summaries = [];

        foreach ($this->files as $path => $file) {
            $summaries[] = new FileSummary(
                file: $path,
                count: $file['count'],
                max: $file['max'],
                average: $file['sum'] / $file['count'],
                exceeded: $file['exceeded'],
            );
        }

        usort($summaries, fn (FileSummary $a, FileSummary $b) => [$b->exceeded, $b->max] <=> [$a->exceeded, $a->max]);

        return array_slice($summaries, 0, $top);
    }
}
