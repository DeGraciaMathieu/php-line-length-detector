<?php

declare(strict_types=1);

namespace App\Baselines;

use App\Dtos\Line;
use InvalidArgumentException;

class Baseline
{
    /**
     * Number of known occurrences by file and fingerprint.
     *
     * @param array<string, array<string, int>> $lines
     */
    public function __construct(
        private array $lines = [],
    ) {}

    public static function fromFile(string $path): self
    {
        $content = is_readable($path) ? file_get_contents($path) : false;

        if ($content === false) {
            throw new InvalidArgumentException("Unable to read the baseline [$path].");
        }

        $data = json_decode($content, true);

        if (! is_array($data) || ! is_array($data['lines'] ?? null)) {
            throw new InvalidArgumentException("The baseline [$path] is not valid.");
        }

        /** @var array<string, array<string, int>> $lines */
        $lines = $data['lines'];

        return new self($lines);
    }

    /**
     * @param list<Line> $lines
     */
    public static function fromLines(array $lines): self
    {
        $baseline = new self();

        foreach ($lines as $line) {
            $baseline->lines[$line->file][$line->getFingerprint()] ??= 0;
            $baseline->lines[$line->file][$line->getFingerprint()]++;
        }

        return $baseline;
    }

    /**
     * Tells whether the line is known, and uses up one of its occurrences.
     */
    public function consume(Line $line): bool
    {
        $occurrences = $this->lines[$line->file][$line->getFingerprint()] ?? 0;

        if ($occurrences === 0) {
            return false;
        }

        $this->lines[$line->file][$line->getFingerprint()] = $occurrences - 1;

        return true;
    }

    public function save(string $path): void
    {
        ksort($this->lines);

        $json = json_encode(['lines' => $this->lines], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if (@file_put_contents($path, $json . PHP_EOL) === false) {
            throw new InvalidArgumentException("Unable to write the baseline [$path].");
        }
    }
}
