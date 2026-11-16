<?php

declare(strict_types=1);

namespace App\Commands;

use Generator;
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

        $files = $this->getFilesFromPath();

        $lengthBag = new LengthBag(
            Thresholds::fromString($this->option('thresholds'))
        );

        $exceededLineBag = $this->makeExceededLineBag();

        $this->analyseFiles($files, $lengthBag, $exceededLineBag);

        app(ViewRenderer::class)->display($lengthBag, $exceededLineBag, $this->getTop());

        if ($exceededLineBag?->isEmpty() === false) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function makeExceededLineBag(): ?ExceededLineBag
    {
        $max = $this->option('max');

        if ($max === null) {
            return null;
        }

        return new ExceededLineBag((int) $max);
    }

    private function getTop(): ?int
    {
        $top = $this->option('top');

        return $top === null ? null : (int) $top;
    }

    private function getFilesFromPath(): Generator
    {
        $fileFinder = new FileFinder(
            basePath: getcwd(),
        );

        $path = $this->argument('path');

        return $fileFinder->getFiles(
            paths: explode(',', $path),
        );
    }

    private function analyseFiles(Generator $files, LengthBag $lengthBag, ?ExceededLineBag $exceededLineBag): void
    {
        foreach ($files as $file) {

            $lines = file($file->fullPath);

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
