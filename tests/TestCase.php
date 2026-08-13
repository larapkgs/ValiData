<?php

declare(strict_types=1);

namespace LaraPkgs\ValiData\Tests;

use LaraPkgs\ValiData\ValiDataServiceProvider;
use LaraPkgs\Validation\ValidationServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        //
    }

    protected function getPackageProviders($app): array
    {
        return [
            ValiDataServiceProvider::class,
            ValidationServiceProvider::class,
        ];
    }
}
