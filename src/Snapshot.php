<?php

declare(strict_types=1);

namespace LaraPkgs\ValiData;

use LaraPkgs\ValiData\Concerns\HasValidData;
use LaraPkgs\ValiData\Contracts\Schema;
use LaraPkgs\ValiData\Contracts\ValidData;

final class Snapshot implements ValidData
{
    use HasValidData;

    /**
     * @param  array<array-key, mixed>  $payload
     */
    public function __construct(array $payload, ?Schema $schema = null)
    {
        $this->applyPayload($payload, $schema);
    }
}
