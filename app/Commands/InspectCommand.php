<?php

declare(strict_types=1);

namespace App\Commands;

use Generator;
use LaravelZero\Framework\Commands\Command;
use DeGraciaMathieu\FileExplorer\FileFinder;
use App\Bags\LengthBag;
use App\Dtos\Thresholds;
use App\Renderers\ViewRenderer;

class InspectCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'inspect {path} {--thresholds=160,120,80,60,30}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('❀ PHP Line Lenght Detector ❀');

        $files = $this->getFilesFromPath();

        $lengthBag = $this->getLengthBagFromFiles($files);

        app(ViewRenderer::class)->display($lengthBag);
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

    private function getLengthBagFromFiles(Generator $files): LengthBag
    {
        $lengthBag = new LengthBag(
            Thresholds::fromString($this->option('thresholds'))
        );

        foreach ($files as $file) {

            $lines = file($file->fullPath);

            foreach ($lines as $line) {

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
            }
        }

        return $lengthBag;
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
