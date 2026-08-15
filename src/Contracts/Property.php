<?php

declare(strict_types=1);

namespace LaraPkgs\ValiData\Contracts;

use LaraPkgs\Validation\ValidatableCollection;

interface Property
{
    public function getName(): string;

    /**
     * @param  array<array-key, mixed>  $payload
     * @return array<array-key, mixed>
     */
    public function applyDefault(array $payload): array;

    public function getValidation(): ValidatableCollection;
}
