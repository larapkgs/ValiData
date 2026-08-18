<?php

declare(strict_types=1);

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
final class SchemaBuilder implements Countable, IteratorAggregate, SchemaContract
{
    use IsValidatable;

    /** @var Collection<string,Property>|null */
    protected ?Collection $items = null;

    public static function make(Property ...$items): self
    {
        return new self(...$items);
    }

    public function __construct(Property ...$items)
    {
        $this->processItems(...$items);
    }

    public function __clone()
    {
        $this->items = Collection::make($this->all());
    }

    protected function newInstance(?Closure $callback = null): self
    {
        $instance = clone $this;

        return $callback !== null ? tap($instance, $callback) : $instance;
    }

    /**
     * @return Collection<string, Property>
     */
    public function getItems(): Collection
    {
        return Collection::make($this->all());
    }

    /**
     * @return Collection<string, Property>
     */
    protected function resolveItems(): Collection
    {
        return $this->items ??= Collection::make();
    }

    public function add(Property ...$items): self
    {
        return $this->newInstance(function (self $instance) use ($items) {
            return $instance->processItems(...$items);
        });
    }

    protected function processItems(Property ...$items): self
    {
        Collection::make($items)
            ->transform(fn (Property $item) => clone $item)
            ->each(fn (Property $item) => $this->resolveItems()->put($item->getName(), $item));

        return $this;
    }

    /**
     * @return array<string, Property>
     */
    public function all(): array
    {
        return $this->resolveItems()->map(function (Property $item) {
            return clone $item;
        })->all();
    }

    public function applyDefaults(array $payload): array
    {
        return $this->resolveItems()->reduce(function (array $payload, Property $property) {
            return $property->applyDefault($payload);
        }, $payload);
    }

    public function applyPayload(array $payload): array
    {
        return $this->resolveItems()->reduce(function (array $data, Property $property) use ($payload) {
            return $property->applyPayload($payload, $data);
        }, []);
    }

    public function makeValidator(array $data): Validator
    {
        return $this->getValidatableCollection()->makeValidator($data);
    }

    public function getValidatableCollection(): ValidatableCollection
    {
        $items = $this->resolveItems()->map(function (Property $item) {
            return $item->getValidation();
        })->all();

        return ValidatableCollection::make()->merge(...$items);
    }

    public function applyCasts(array $data): array
    {
        return $this->resolveItems()->reduce(function (array $data, Property $property) {
            return $property->applyCast($data);
        }, $data);
    }

    // Countable implementation
    public function count(): int
    {
        return $this->resolveItems()->count();
    }

    // IteratorAggregate Implementation
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->all());
    }
}
