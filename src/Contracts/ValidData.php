<?php

namespace LaraPkgs\ValiData\Contracts;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use IteratorAggregate;
use JsonSerializable;
use Stringable;

/**
 * @extends Arrayable<array-key, mixed>
 * @extends ArrayAccess<array-key, mixed>
 * @extends IteratorAggregate<array-key, mixed>
 */
interface ValidData extends Arrayable, ArrayAccess, Countable, IteratorAggregate, Jsonable, JsonSerializable, Responsable, Stringable
{
    public function get(string $property): mixed;

    public function has(string $property): bool;

    /**
     * @return array<array-key, mixed>
     */
    public function all(): array;
}
