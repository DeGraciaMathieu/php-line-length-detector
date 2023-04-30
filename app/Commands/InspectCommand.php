<?php

declare(strict_types=1);

namespace App\Commands;

use Generator;
use LaravelZero\Framework\Commands\Command;
use function Termwind\{render};
use DeGraciaMathieu\FileExplorer\FileFinder;
use App\Aggregators;
use Illuminate\Support\Facades\View;

class InspectCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'inspect {path} {--steps=30,80,120}';

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

        [$distribution, $statistic] = $this->analyseFiles($files);

        $view = View::make('inspect', [
            'distribution' => $distribution,
            'statistic' => $statistic,
        ])->render();

        render($view);
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

    private function analyseFiles(Generator $files): array
    {
        $steps = $this->option('steps');

        $distribution = new Aggregators\Distribution(
            steps: explode(',', $steps),
        );

        $statistic = new Aggregators\Statistic();

        foreach ($files as $file) {

            $lines = file($file->fullPath);

            foreach ($lines as $line) {

                $line = $this->removingLineBreaks($line);

                if ($this->unwantedLine($line)) {
                    continue;
                }

                $lenght = strlen($line);

                $distribution->add($lenght);

                $statistic->add($lenght);
            }
        }

        return [$distribution, $statistic];
    }

    private function removingLineBreaks(string $line): string
    {
        return preg_replace('~[\r\n]+~', '', $line);
    }

    private function unwantedLine(string $line): bool
    {
        return $line === '' OR $line === '<?php';
    }
}
