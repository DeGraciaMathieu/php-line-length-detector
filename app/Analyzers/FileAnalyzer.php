<?php

declare(strict_types=1);

namespace App\Analyzers;

use App\Dtos\Line;
use Generator;
use RuntimeException;
use DeGraciaMathieu\FileExplorer\File;

class FileAnalyzer
{
    private const MIN_LENGTH = 10;

    /**
     * @return Generator<int, Line>
     */
    public function getLines(File $file): Generator
    {
        $lines = is_readable($file->fullPath) ? file($file->fullPath) : false;

        if ($lines === false) {
            throw new RuntimeException("Unable to read [$file->displayPath].");
        }

        foreach ($lines as $index => $line) {

            $line = trim($line);

            $line = $this->removingLineBreaks($line);

            if ($this->unwantedLine($line)) {
                continue;
            }

            $length = strlen($line);

            if ($this->lineIsTooShort($length)) {
                continue;
            }

            yield new Line($file->displayPath, $index + 1, $length);
        }
    }

    private function removingLineBreaks(string $line): string
    {
        return (string) preg_replace('~[\r\n]+~', '', $line);
    }

    private function unwantedLine(string $line): bool
    {
        return $line === 'return;'
            || $line === 'continue;'
            || str_starts_with($line, '#')
            || str_starts_with($line, '*')
            || str_starts_with($line, '/*')
            || str_starts_with($line, '//')
            || str_starts_with($line, 'use');
    }

    private function lineIsTooShort(int $length): bool
    {
        return $length < self::MIN_LENGTH;
    }
}
