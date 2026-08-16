<?php

namespace LaraPkgs\ValiData\Contracts;

use LaraPkgs\Validation\Contracts\Validatable;

interface Schema extends Validatable
{
    /**
     * @param  array<array-key, mixed>  $payload
     * @return array<array-key, mixed>
     */
    public function applyDefaults(array $payload): array;

    /**
     * @param  array<array-key, mixed>  $payload
     * @return array<array-key, mixed>
     */
    public function applyPayload(array $payload): array;
}
