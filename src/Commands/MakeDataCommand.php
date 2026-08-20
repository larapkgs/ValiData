<?php

declare(strict_types=1);

namespace LaraPkgs\ValiData\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use LaraPkgs\ValiData\Support\Generator;

final class MakeDataCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'make:data {name : The name of the data class} {--force : Overwrite the data class if it already exists}';

    /**
     * @var string
     */
    protected $description = 'Create a new data class';

    public function handle(): int
    {
        $generator = $this->makeGenerator();

        return (! $generator->isOverwriting() && $generator->exists())
            ? $this->handleFailure($generator)
            : $this->handleSuccess($generator);
    }

    protected function handleFailure(Generator $generator): int
    {
        $this->components->error(sprintf('File [%s] already exists.', $generator->getPath()));

        return self::FAILURE;
    }

    protected function handleSuccess(Generator $generator): int
    {
        $generator->generate();

        $this->components->info(sprintf('File [%s] created successfully', $generator->getPath()));

        return self::SUCCESS;
    }

    protected function makeGenerator(): Generator
    {
        /** @var string $fileInput */
        $fileInput = $this->argument('name');
        $stubs = $this->getStubs();

        /** @var array<string, mixed> $config */
        $config = Config::get('vali-data.generators.data');
        if ((bool) $this->option('force')) {
            $config['overwrite'] = true;
        }

        return new Generator($fileInput, ...$stubs)->applyConfig($config);
    }

    /**
     * @return array<int, string>
     */
    protected function getStubs(): array
    {
        $publishedStubPath = $this->laravel->basePath('stubs/data.stub');
        $defaultStubPath = __DIR__ . '/../../stubs/data.stub';

        return [$publishedStubPath, $defaultStubPath];
    }
}
