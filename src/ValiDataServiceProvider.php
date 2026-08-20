<?php

declare(strict_types=1);

namespace LaraPkgs\ValiData;

use Illuminate\Support\ServiceProvider;
use LaraPkgs\ValiData\Commands\MakeDataCommand;

final class ValiDataServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/vali-data.php', 'vali-data'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/vali-data.php' => config_path('vali-data.php'),
            ], 'larapkgs-vali-data-config');

            $this->publishes([
                __DIR__ . '/../stubs' => base_path('stubs'),
            ], 'larapkgs-vali-data-stubs');

            $this->commands([
                MakeDataCommand::class,
            ]);
        }
    }
}
