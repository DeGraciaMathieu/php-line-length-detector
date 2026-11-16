<?php

declare(strict_types=1);

namespace App\Enums;

enum Format: string
{
    case Text = 'text';
    case Json = 'json';
    case Checkstyle = 'checkstyle';
    case Github = 'github';

    /**
     * These formats report the exceeded lines, so they need --max.
     */
    public function requiresMax(): bool
    {
        return $this === self::Checkstyle || $this === self::Github;
    }
}
