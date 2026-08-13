<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;
use LaraPkgs\ValiData\ValiDataServiceProvider;

it('registers the service provider successfully', function () {
    $providers = App::getProviders(ValiDataServiceProvider::class);

    expect($providers)
        ->toHaveKey(ValiDataServiceProvider::class)
        ->and($providers[ValiDataServiceProvider::class])->toBeInstanceOf(ValiDataServiceProvider::class);
});
