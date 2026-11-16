<?php

declare(strict_types=1);

namespace App\Commands;

use Generator;
use InvalidArgumentException;
use RuntimeException;
use LaravelZero\Framework\Commands\Command;
use DeGraciaMathieu\FileExplorer\File;
use DeGraciaMathieu\FileExplorer\FileFinder;
use App\Analyzers\FileAnalyzer;
use App\Bags\LengthBag;
use App\Bags\ExceededLineBag;
use App\Dtos\Thresholds;
use App\Renderers\ViewRenderer;

class InspectCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'inspect {path} {--thresholds=160,120,80,60,30} {--max=} {--top=} {--without-indentation} {--tab-width=4} {--min-length=10} {--include-comments} {--exclude=*}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Analyze the line length of PHP files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('❀ PHP Line Length Detector ❀');

        try {
            $paths = $this->getPaths();
            $excludePatterns = $this->getExcludePatterns();
            $thresholds = Thresholds::fromString($this->getStringOption('thresholds') ?? '');
            $exceededLineBag = $this->makeExceededLineBag();
            $top = $this->getIntegerOption('top');
            $fileAnalyzer = $this->makeFileAnalyzer();
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::INVALID;
        }

        $files = $this->getFilesFromPaths($paths, $excludePatterns);

        $lengthBag = new LengthBag($thresholds);

        $this->analyseFiles($fileAnalyzer, $files, $lengthBag, $exceededLineBag);

        if ($lengthBag->getCount() === 0) {
            $this->warn('No line to analyze.');

            return self::SUCCESS;
        }

        app(ViewRenderer::class)->display($lengthBag, $exceededLineBag, $top);

        if ($exceededLineBag?->isEmpty() === false) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function getPaths(): array
    {
        $path = $this->argument('path');

        if (! is_string($path)) {
            throw new InvalidArgumentException('The path must be a string.');
        }

        $paths = explode(',', $path);

        foreach ($paths as $path) {
            if (! file_exists($path)) {
                throw new InvalidArgumentException("The path [$path] does not exist.");
            }
        }

        return $paths;
    }

    /**
     * @return list<string>
     */
    private function getExcludePatterns(): array
    {
        $patterns = $this->option('exclude');

        if (! is_array($patterns)) {
            return [];
        }

        $patterns = array_values(array_filter($patterns, 'is_string'));

        foreach ($patterns as $pattern) {
            if (@preg_match("#{$pattern}#", '') === false) {
                throw new InvalidArgumentException("The exclude pattern [$pattern] is not a valid regular expression.");
            }
        }

        return $patterns;
    }

    private function makeExceededLineBag(): ?ExceededLineBag
    {
        $max = $this->getIntegerOption('max');

        if ($max === null) {
            return null;
        }

        return new ExceededLineBag($max);
    }

    private function getStringOption(string $name): ?string
    {
        $value = $this->option($name);

        if ($value !== null && ! is_string($value)) {
            throw new InvalidArgumentException("The --$name option must be a string.");
        }

        return $value;
    }

    private function makeFileAnalyzer(): FileAnalyzer
    {
        return new FileAnalyzer(
            withIndentation: ! $this->option('without-indentation'),
            tabWidth: $this->getIntegerOption('tab-width') ?? 4,
            minLength: $this->getIntegerOption('min-length', min: 0) ?? 10,
            includeComments: (bool) $this->option('include-comments'),
        );
    }

    private function getIntegerOption(string $name, int $min = 1): ?int
    {
        $value = $this->getStringOption($name);

        if ($value === null) {
            return null;
        }

        if (! ctype_digit($value) || (int) $value < $min) {
            throw new InvalidArgumentException("The --$name option must be an integer greater than or equal to $min.");
        }

        return (int) $value;
    }

    /**
     * @param list<string> $paths
     * @param list<string> $excludePatterns
     * @return Generator<int, File>
     */
    private function getFilesFromPaths(array $paths, array $excludePatterns): Generator
    {
        $fileFinder = new FileFinder(
            basePath: getcwd() ?: '.',
            ignorePatterns: $excludePatterns,
        );

        return $fileFinder->getFiles(
            paths: $paths,
        );
    }

    /**
     * @param Generator<int, File> $files
     */
    private function analyseFiles(
        FileAnalyzer $fileAnalyzer,
        Generator $files,
        LengthBag $lengthBag,
        ?ExceededLineBag $exceededLineBag,
    ): void {
        foreach ($files as $file) {
            try {
                foreach ($fileAnalyzer->getLines($file) as $line) {
                    $lengthBag->add($line->length);
                    $exceededLineBag?->add($line);
                }
            } catch (RuntimeException $e) {
                $this->warn($e->getMessage());
            }
        }
    }
}
