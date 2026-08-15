<?php

declare(strict_types=1);

namespace LaraPkgs\ValiData\Contracts;

use LaraPkgs\Validation\ValidatableCollection;

interface Property
{
    public function getName(): string;

    public function applyDefault(array $payload): array;

    public function getValidation(): ValidatableCollection;
}
