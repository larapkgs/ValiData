<?php

namespace LaraPkgs\ValiData\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use LaraPkgs\ValiData\Support\Generator;

abstract class GeneratorCommand extends Command
{
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
        $stubs = Arr::wrap($this->getStubs());

        /** @var array<string, mixed> $config */
        $config = $this->getConfig();
        if ((bool) $this->option('force')) {
            $config['overwrite'] = true;
        }

        return new Generator($fileInput, ...$stubs)->applyConfig($config);
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function getConfig(): array;

    /**
     * @return string|array<int, string>
     */
    abstract protected function getStubs(): string|array;
}
