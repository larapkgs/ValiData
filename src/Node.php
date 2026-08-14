<?php

declare(strict_types=1);

namespace LaraPkgs\ValiData;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use IteratorAggregate;
use JsonSerializable;
use Stringable;
use Traversable;

/**
 * @implements Arrayable<array-key, mixed>
 * @implements ArrayAccess<array-key, mixed>
 * @implements IteratorAggregate<array-key, mixed>
 */
class Node implements Arrayable, ArrayAccess, Countable, IteratorAggregate, Jsonable, JsonSerializable, Responsable, Stringable
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

    public function __toString(): string
    {
        return $this->toJson();
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

    /**
     * @return array<array-key, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    // Arrayable implementation

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(): array
    {
        return $this->serializeArray($this->all());
    }

    /**
     * @param  array<array-key, mixed>  $array
     * @return array<array-key, mixed>
     */
    protected function serializeArray(array $array): array
    {
        return Collection::make($array)
            ->map(fn (mixed $value): mixed => $this->serializeValue($value))
            ->all();
    }

    protected function serializeValue(mixed $value): mixed
    {
        return match (true) {
            is_array($value) => $this->serializeArray($value),
            $value instanceof Arrayable => $value->toArray(),
            $value instanceof Jsonable => json_decode($value->toJson(), true),
            $value instanceof JsonSerializable => (array) $value->jsonSerialize(),
            default => $value
        };
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

    // Countable implementation
    public function count(): int
    {
        return count($this->data);
    }

    // IteratorAggregate implementation
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->all());
    }

    // Jsonable implementation
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options | JSON_THROW_ON_ERROR);
    }

    // JsonSerializable implementation
    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // Responsable implementation
    public function toResponse($request)
    {
        return new JsonResponse(
            data: $this->toArray(),
            status: 200,
        );
    }
}
