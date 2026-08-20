<?php

declare(strict_types=1);

namespace Tests\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

function callArtisanCommand($parameters = []): int
{
    $parameters = array_merge(['name' => 'User'], $parameters);

    return Artisan::call('make:data', $parameters);
}

function cleanup()
{
    if (File::isDirectory($directory = app_path('Data'))) {
        File::deleteDirectory($directory);
    }

    if (File::isDirectory($directory = base_path('stubs'))) {
        File::deleteDirectory($directory);
    }
}

beforeEach(function () {
    $this->file = app_path('Data/UserData.php');
    cleanup();
});

afterEach(function () {
    cleanup();
});

it('creates a new data class using configuration values', function (): void {
    expect(callArtisanCommand())->toBe(Command::SUCCESS);

    expect(File::get($this->file))
        ->toContain('namespace App\Data;')
        ->toContain('final class UserData extends ValidData')
        ->toContain('protected function makeSchema(): Schema');
});

it('prevents creating a data class that already exists', function () {
    expect(callArtisanCommand())->toBe(Command::SUCCESS);
    expect(callArtisanCommand())->toBe(Command::FAILURE);
});

it('allows creating a data class that already exists when the --force option is set', function () {
    expect(callArtisanCommand())->toBe(Command::SUCCESS);
    expect(callArtisanCommand(['--force' => true]))->toBe(Command::SUCCESS);
});

it('uses the published data.stub when available', function (): void {
    $stubPath = base_path('stubs/data.stub');
    expect(File::exists($stubPath))->toBeFalse();

    Artisan::call('vendor:publish', ['--tag' => 'larapkgs-vali-data-stubs']);
    expect(File::exists($stubPath))->toBeTrue();

    $stubContent = File::get($stubPath);
    $line = "\n// This is a published stub.";

    File::put($stubPath, $stubContent . $line);
    expect(File::get($stubPath))->toContain($line);

    expect(callArtisanCommand())->toBe(0);
    expect(File::exists($this->file))->toBeTrue();

    expect(File::get($this->file))
        ->toContain('namespace App\Data;')
        ->toContain('final class UserData extends ValidData')
        ->toContain('protected function makeSchema(): Schema')
        ->toContain($line);
});
