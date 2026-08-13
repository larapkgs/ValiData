<?php

declare(strict_types=1);

namespace LaraPkgs\ValiData\Contracts;

use LaraPkgs\Validation\ValidatableCollection;

interface Property
{
    public function getName(): string;

    public function hasDefaultValue(): bool;

    public function getDefaultValue(): mixed;

    public function getValidation(): ValidatableCollection;
}
