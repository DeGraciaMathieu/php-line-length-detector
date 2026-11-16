<?php

declare(strict_types=1);

namespace App\Configs;

use InvalidArgumentException;

class ConfigFile
{
    public const DEFAULT_PATH = 'php-line-length-detector.json';

    private const FLAGS = ['without-indentation', 'include-comments', 'by-file'];

    private const VALUES = ['thresholds', 'max', 'top', 'tab-width', 'min-length', 'format', 'baseline'];

    private const LISTS = ['paths', 'exclude'];

    /**
     * @param array<string, bool|string|list<string>> $options
     */
    public function __construct(
        public readonly array $options = [],
    ) {}

    public static function fromFile(string $path): self
    {
        $content = is_readable($path) ? file_get_contents($path) : false;

        if ($content === false) {
            throw new InvalidArgumentException("Unable to read the config file [$path].");
        }

        $data = json_decode($content, true);

        if (! is_array($data)) {
            throw new InvalidArgumentException("The config file [$path] is not a valid JSON object.");
        }

        $options = [];

        foreach ($data as $name => $value) {
            $options[(string) $name] = self::normalize((string) $name, $value);
        }

        return new self($options);
    }

    /**
     * Brings the value to the shape the command option would have.
     *
     * @return bool|string|list<string>
     */
    private static function normalize(string $name, mixed $value): bool|string|array
    {
        if (in_array($name, self::FLAGS, true) && is_bool($value)) {
            return $value;
        }

        if (in_array($name, self::VALUES, true) && (is_string($value) || is_int($value))) {
            return (string) $value;
        }

        if ($name === 'thresholds' && is_array($value) && array_filter($value, 'is_int') === $value) {
            return implode(',', $value);
        }

        if (in_array($name, self::LISTS, true) && is_array($value) && array_filter($value, 'is_string') === $value) {
            return array_values($value);
        }

        throw new InvalidArgumentException("The config option [$name] is unknown or has an invalid value.");
    }
}
