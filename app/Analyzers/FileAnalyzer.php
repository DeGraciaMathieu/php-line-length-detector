<?php

declare(strict_types=1);

namespace App\Analyzers;

use App\Dtos\Line;
use Generator;
use RuntimeException;
use DeGraciaMathieu\FileExplorer\File;

class FileAnalyzer
{
    public function __construct(
        private readonly bool $withIndentation = true,
        private readonly int $tabWidth = 4,
        private readonly int $minLength = 10,
        private readonly bool $includeComments = false,
    ) {}

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

            $line = rtrim($line);

            $content = ltrim($line);

            if ($this->isIgnored($content)) {
                continue;
            }

            $length = $this->measure($this->withIndentation ? $line : $content);

            yield new Line($file->displayPath, $index + 1, $length);
        }
    }

    private function isIgnored(string $content): bool
    {
        return $content === ''
            || mb_strlen($content) < $this->minLength
            || $this->isImport($content)
            || (! $this->includeComments && $this->isComment($content));
    }

    private function isImport(string $content): bool
    {
        return preg_match('~^use\s~', $content) === 1;
    }

    private function isComment(string $content): bool
    {
        $isAttribute = str_starts_with($content, '#[');

        return (str_starts_with($content, '#') && ! $isAttribute)
            || str_starts_with($content, '*')
            || str_starts_with($content, '/*')
            || str_starts_with($content, '//');
    }

    /**
     * Tabs are expanded to the next tab stop, as an editor displays them.
     */
    private function measure(string $line): int
    {
        $length = 0;

        foreach (mb_str_split($line) as $character) {
            $length += $character === "\t"
                ? $this->tabWidth - ($length % $this->tabWidth)
                : 1;
        }

        return $length;
    }
}
