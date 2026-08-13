<?php

namespace LaraPkgs\ValiData;

use ArrayAccess;
use Exception;

/**
 * @implements ArrayAccess<array-key, mixed>
 */
class Node implements ArrayAccess
{
    /** @var array<array-key, mixed> */
    protected $data;

    /**
     * @param  array<array-key, mixed>  $payload
     */
    public function __construct(array $payload)
    {
        $this->data = $payload;
    }

    public function __get(string $property): mixed
    {
        return $this->get($property);
    }

    public function __isset(string $property): bool
    {
        return $this->has($property);
    }

    public function get(string $property): mixed
    {
        if (! $this->has($property)) {
            throw new Exception(sprintf("Property '%s' does not exist.", $property));
        }

        return data_get($this->data, $property);
    }

    public function has(string $property): bool
    {
        return data_has($this->data, $property);
    }

    // ArrayAccess implementation
    public function offsetExists(mixed $offset): bool
    {
        $offset = $this->offsetToString($offset);

        return $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        $offset = $this->offsetToString($offset);

        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Exception(sprintf("Cannot set the value of '%s' because this is a readonly object.", $offset));
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new Exception(sprintf("Cannot unset '%s' because this is a readonly object.", $offset));
    }

    protected function offsetToString(mixed $offset): string
    {
        if (is_string($offset)) {
            return $offset;
        }

        throw new Exception('Offset must be a string');
    }
}
