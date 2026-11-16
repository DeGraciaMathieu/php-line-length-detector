<?php

declare(strict_types=1);

namespace App\Commands;

use Generator;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use DeGraciaMathieu\FileExplorer\FileFinder;
use App\Bags\LengthBag;
use App\Bags\ExceededLineBag;
use App\Dtos\Line;
use App\Dtos\Thresholds;
use App\Renderers\ViewRenderer;

class InspectCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'inspect {path} {--thresholds=160,120,80,60,30} {--max=} {--top=}';

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
            $thresholds = Thresholds::fromString($this->option('thresholds'));
            $exceededLineBag = $this->makeExceededLineBag();
            $top = $this->getPositiveIntegerOption('top');
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::INVALID;
        }

        $files = $this->getFilesFromPaths($paths);

        $lengthBag = new LengthBag($thresholds);

        $this->analyseFiles($files, $lengthBag, $exceededLineBag);

        if ($lengthBag->count === 0) {
            $this->warn('No line to analyze.');

            return self::SUCCESS;
        }

        app(ViewRenderer::class)->display($lengthBag, $exceededLineBag, $top);

        if ($exceededLineBag?->isEmpty() === false) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function getPaths(): array
    {
        $paths = explode(',', $this->argument('path'));

        foreach ($paths as $path) {
            if (! file_exists($path)) {
                throw new InvalidArgumentException("The path [$path] does not exist.");
            }
        }

        return $paths;
    }

    private function makeExceededLineBag(): ?ExceededLineBag
    {
        $max = $this->getPositiveIntegerOption('max');

        if ($max === null) {
            return null;
        }

        return new ExceededLineBag($max);
    }

    private function getPositiveIntegerOption(string $name): ?int
    {
        $value = $this->option($name);

        if ($value === null) {
            return null;
        }

        if (! ctype_digit($value) || (int) $value === 0) {
            throw new InvalidArgumentException("The --$name option must be a positive integer.");
        }

        return (int) $value;
    }

    private function getFilesFromPaths(array $paths): Generator
    {
        $fileFinder = new FileFinder(
            basePath: getcwd(),
        );

        return $fileFinder->getFiles(
            paths: $paths,
        );
    }

    private function analyseFiles(Generator $files, LengthBag $lengthBag, ?ExceededLineBag $exceededLineBag): void
    {
        foreach ($files as $file) {

            $lines = file($file->fullPath);

            if ($lines === false) {
                $this->warn("Unable to read [$file->displayPath].");

                continue;
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

                $lengthBag->add($length);

                $exceededLineBag?->add(
                    new Line($file->displayPath, $index + 1, $length),
                );
            }
        }
    }

    private function removingLineBreaks(string $line): string
    {
        return preg_replace('~[\r\n]+~', '', $line);
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
        return $length < 10;
    }
}
