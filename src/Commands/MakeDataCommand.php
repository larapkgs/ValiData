<?php

declare(strict_types=1);

namespace LaraPkgs\ValiData\Commands;

use Illuminate\Support\Facades\Config;

final class MakeDataCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $signature = 'make:data {name : The name of the data class} {--force : Overwrite the data class if it already exists}';

    /**
     * @var string
     */
    protected $description = 'Create a new data class';

    protected function getConfig(): array
    {
        /** @var array<string, mixed> $config */
        $config = Config::get('vali-data.generators.data');

        return $config;
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
