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
use App\Baselines\Baseline;
use App\Configs\ConfigFile;
use App\Bags\LengthBag;
use App\Bags\ExceededLineBag;
use App\Bags\FileBag;
use App\Dtos\Report;
use App\Dtos\Thresholds;
use App\Enums\Format;
use App\Renderers\Renderer;
use App\Renderers\ViewRenderer;
use App\Renderers\JsonRenderer;
use App\Renderers\CheckstyleRenderer;
use App\Renderers\GithubRenderer;

class InspectCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'inspect
        {path? : Comma separated paths to analyze, required unless set in the config file}
        {--thresholds=160,120,80,60,30 : Lengths to count lines against}
        {--max= : Fail if a line exceeds this length, and list those lines}
        {--top= : Limit the listed lines and files}
        {--without-indentation : Do not count the indentation}
        {--tab-width=4 : Width of a tab}
        {--min-length=10 : Ignore lines shorter than this, indentation excluded}
        {--include-comments : Analyze comments too}
        {--exclude=* : Skip files whose absolute path matches this regex}
        {--by-file : Display a summary by file}
        {--format=text : Output format: text, json, checkstyle or github}
        {--baseline= : Ignore the exceeded lines listed in this baseline file}
        {--generate-baseline= : Write the current exceeded lines into this baseline file}
        {--config= : Config file, php-line-length-detector.json in the current directory by default}';

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
        try {
            $config = $this->loadConfig();
            $format = $this->getFormat();
            $paths = $this->getPaths($config);
            $excludePatterns = $this->getExcludePatterns();
            $thresholds = Thresholds::fromString($this->getStringOption('thresholds') ?? '');
            $exceededLineBag = $this->makeExceededLineBag();
            $fileBag = $this->makeFileBag();
            $top = $this->getIntegerOption('top');
            $fileAnalyzer = $this->makeFileAnalyzer();
            $baselinePath = $this->getBaselinePathToGenerate();
        } catch (InvalidArgumentException $e) {
            $this->writeError("<error>{$e->getMessage()}</error>");

            return self::INVALID;
        }

        if ($format === Format::Text) {
            $this->info('❀ PHP Line Length Detector ❀');
        }

        $files = $this->getFilesFromPaths($paths, $excludePatterns);

        $lengthBag = new LengthBag($thresholds);

        $this->analyseFiles($fileAnalyzer, $files, $lengthBag, $exceededLineBag, $fileBag);

        if ($baselinePath !== null && $exceededLineBag !== null) {
            return $this->generateBaseline($baselinePath, $exceededLineBag);
        }

        if ($lengthBag->getCount() === 0 && $format === Format::Text) {
            $this->warn('No line to analyze.');

            return self::SUCCESS;
        }

        $this->makeRenderer($format)->display(
            new Report($lengthBag, $exceededLineBag, $fileBag, $top),
        );

        if ($exceededLineBag?->isEmpty() === false) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function getFormat(): Format
    {
        $value = $this->getStringOption('format') ?? '';

        $format = Format::tryFrom($value);

        if ($format === null) {
            throw new InvalidArgumentException("The format [$value] is not supported.");
        }

        if ($format->requiresMax() && $this->option('max') === null) {
            throw new InvalidArgumentException("The $value format requires the --max option.");
        }

        return $format;
    }

    private function makeRenderer(Format $format): Renderer
    {
        return match ($format) {
            Format::Text => app(ViewRenderer::class),
            Format::Json => new JsonRenderer($this->output),
            Format::Checkstyle => new CheckstyleRenderer($this->output),
            Format::Github => new GithubRenderer($this->output),
        };
    }

    /**
     * Messages go to stderr, so that they never corrupt a machine readable output.
     */
    private function writeError(string $message): void
    {
        $this->output->getErrorStyle()->writeln($message);
    }

    /**
     * Fills the options missing from the command line with the config file ones.
     */
    private function loadConfig(): ConfigFile
    {
        $path = $this->getStringOption('config');

        if ($path === null && ! file_exists(ConfigFile::DEFAULT_PATH)) {
            return new ConfigFile();
        }

        $config = ConfigFile::fromFile($path ?? ConfigFile::DEFAULT_PATH);

        foreach ($config->options as $name => $value) {
            if ($name !== 'paths' && ! $this->input->hasParameterOption("--$name")) {
                $this->input->setOption($name, $value);
            }
        }

        return $config;
    }

    /**
     * @return list<string>
     */
    private function getPaths(ConfigFile $config): array
    {
        $path = $this->argument('path');

        $paths = match (true) {
            is_string($path) => explode(',', $path),
            is_array($config->options['paths'] ?? null) => $config->options['paths'],
            default => throw new InvalidArgumentException('No path to analyze, give one or set "paths" in the config file.'),
        };

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
        $baseline = $this->getBaseline();

        $max = $this->getIntegerOption('max');

        if ($max === null) {
            return null;
        }

        return new ExceededLineBag($max, $baseline);
    }

    private function getBaseline(): ?Baseline
    {
        $path = $this->getStringOption('baseline');

        if ($path === null || $this->getStringOption('generate-baseline') !== null) {
            return null;
        }

        $this->ensureMaxIsGiven('baseline');

        return Baseline::fromFile($path);
    }

    private function getBaselinePathToGenerate(): ?string
    {
        $path = $this->getStringOption('generate-baseline');

        if ($path !== null) {
            $this->ensureMaxIsGiven('generate-baseline');
        }

        return $path;
    }

    private function ensureMaxIsGiven(string $option): void
    {
        if ($this->option('max') === null) {
            throw new InvalidArgumentException("The --$option option requires the --max option.");
        }
    }

    private function generateBaseline(string $path, ExceededLineBag $exceededLineBag): int
    {
        $lines = $exceededLineBag->getLongest();

        try {
            Baseline::fromLines($lines)->save($path);
        } catch (InvalidArgumentException $e) {
            $this->writeError("<error>{$e->getMessage()}</error>");

            return self::FAILURE;
        }

        $this->writeError('<info>Baseline generated in [' . $path . '] with ' . count($lines) . ' lines.</info>');

        return self::SUCCESS;
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

    private function makeFileBag(): ?FileBag
    {
        if (! $this->option('by-file')) {
            return null;
        }

        return new FileBag(tracksExceeded: $this->option('max') !== null);
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
        ?FileBag $fileBag,
    ): void {
        foreach ($files as $file) {
            try {
                foreach ($fileAnalyzer->getLines($file) as $line) {
                    $lengthBag->add($line->length);
                    $exceeded = $exceededLineBag?->add($line) ?? false;
                    $fileBag?->add($line, $exceeded);
                }
            } catch (RuntimeException $e) {
                $this->writeError("<comment>{$e->getMessage()}</comment>");
            }
        }
    }
}
