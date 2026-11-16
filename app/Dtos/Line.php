<?php

declare(strict_types=1);

namespace App\Dtos;

class Line
{
    public function __construct(
        public string $file,
        public int $number,
        public int $length,
        public string $content = '',
    ) {}

    /**
     * Identifies the line by its content rather than its number,
     * which shifts as soon as a line is added above it.
     */
    public function getFingerprint(): string
    {
        return sha1(trim($this->content));
    }
}
