<?php

namespace LaraPkgs\ValiData;

use LaraPkgs\ValiData\Concerns\HasValidData;
use LaraPkgs\ValiData\Contracts\Schema;
use LaraPkgs\ValiData\Contracts\ValidData as ValidDataContract;

abstract class ValidData implements ValidDataContract
{
    use HasValidData;

    /**
     * @param  array<array-key, mixed>  $payload
     */
    public function __construct(array $payload)
    {
        $this->applyPayload($payload, $this->makeSchema());
    }

    abstract protected function makeSchema(): Schema;
}
