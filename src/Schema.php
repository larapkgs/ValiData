<?php

namespace LaraPkgs\ValiData;

use ArrayIterator;
use Closure;
use Countable;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Collection;
use IteratorAggregate;
use LaraPkgs\ValiData\Contracts\Property;
use LaraPkgs\ValiData\Contracts\Schema as SchemaContract;
use LaraPkgs\Validation\Concerns\IsValidatable;
use LaraPkgs\Validation\ValidatableCollection;
use Traversable;

/**
 * @implements IteratorAggregate<string, Property>
 */
abstract class Schema implements Countable, IteratorAggregate, SchemaContract
{
    use IsValidatable;

    /** @var Collection<string,Property>|null */
    protected ?Collection $properties = null;

    public function __clone()
    {
        $this->properties = Collection::make($this->all());
    }

    protected function newInstance(?Closure $callback = null): static
    {
        $instance = clone $this;

        return $callback !== null ? tap($instance, $callback) : $instance;
    }

    /**
     * @return Collection<string, Property>
     */
    public function getProperties(): Collection
    {
        return Collection::make($this->all());
    }

    /**
     * @return Collection<string, Property>
     */
    protected function resolveProperties(): Collection
    {
        if ($this->properties !== null) {
            return $this->properties;
        }

        return $this->properties ??= $this->processProperties(...$this->definition());
    }

    /**
     * @return array<string, Property>
     */
    protected function definition(): array
    {
        return [];
    }

    /**
     * @return Collection<string, Property>
     */
    protected function processProperties(Property ...$items): Collection
    {
        return Collection::make($items)
            ->transform(fn (Property $item) => clone $item)
            ->keyBy(fn (Property $property) => $property->getName());
    }

    /**
     * @return array<string, Property>
     */
    public function all(): array
    {
        return $this->resolveProperties()->map(function (Property $item) {
            return clone $item;
        })->all();
    }

    public function applyDefaults(array $payload): array
    {
        return $this->resolveProperties()->reduce(function (array $payload, Property $property) {
            return $property->applyDefault($payload);
        }, $payload);
    }

    public function applyPayload(array $payload): array
    {
        return $this->resolveProperties()->reduce(function (array $data, Property $property) use ($payload) {
            return $property->applyPayload($payload, $data);
        }, []);
    }

    public function makeValidator(array $data): Validator
    {
        return $this->getValidatableCollection()->makeValidator($data);
    }

    public function getValidatableCollection(): ValidatableCollection
    {
        $items = $this->resolveProperties()->map(function (Property $item) {
            return $item->getValidation();
        })->all();

        return ValidatableCollection::make()->merge(...$items);
    }

    public function applyCasts(array $data): array
    {
        return $this->resolveProperties()->reduce(function (array $data, Property $property) {
            return $property->applyCast($data);
        }, $data);
    }

    // Countable implementation
    public function count(): int
    {
        return $this->resolveProperties()->count();
    }

    // IteratorAggregate Implementation
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->all());
    }
}
