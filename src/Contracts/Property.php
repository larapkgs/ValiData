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

    /**
     * @param  array<array-key, mixed>  $payload
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    public function applyPayload(array $payload, array $data): array;

    public function getValidation(): ValidatableCollection;
}
