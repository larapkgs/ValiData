<?php

namespace LaraPkgs\ValiData\Properties;

use Illuminate\Support\Collection;
use LaraPkgs\ValiData\Contracts\Property;
use LaraPkgs\ValiData\Contracts\Schema;
use LaraPkgs\ValiData\Properties\Concerns\BaseProperty;
use LaraPkgs\ValiData\Snapshot;
use LaraPkgs\Validation\ValidatableBuilder;
use LaraPkgs\Validation\ValidatableCollection;

class ToManyReference implements Property
{
    use BaseProperty;

    protected Schema $schema;

    public function __construct(string $name, Schema $schema)
    {
        $this->name = $name;
        $this->schema = $schema;
    }

    public function applyDefault(array $payload): array
    {
        if (array_key_exists($key = $this->getName(), $payload)) {
            /** @var array<array-key, array<array-key, mixed>> $propertyPayload */
            $propertyPayload = $payload[$key];

            $payload[$key] = Collection::make($propertyPayload)
                ->map(fn (array $item) => $this->schema->applyDefaults($item))
                ->all();
        }

        return $payload;
    }

    public function applyPayload(array $payload, array $data): array
    {
        if (array_key_exists($key = $this->getName(), $payload)) {
            /** @var array<array-key, array<array-key, mixed>> $propertyPayload */
            $propertyPayload = $payload[$key];

            $data[$key] = Collection::make($propertyPayload)
                ->map(fn (array $itemPayload) => $this->schema->applyPayload($itemPayload))
                ->all();
        }

        return $data;
    }

    public function getValidation(): ValidatableCollection
    {
        $validation = clone $this->resolveValidation();

        $childValidation = $this->schema->getValidatableCollection()
            ->prefix($this->getName() . '.*');

        return $validation->merge($childValidation);
    }

    public function makeValidation(): ValidatableCollection
    {
        return ValidatableCollection::make(
            ValidatableBuilder::make($this->name)->array()
        );
    }

    public function applyCast(array $data): array
    {
        if (array_key_exists($key = $this->getName(), $data)) {
            /** @var array<array-key, array<array-key, mixed>> $propertyData */
            $propertyData = $data[$key];

            $data[$key] = Collection::make($propertyData)
                ->map(fn (array $itemData) => $this->schema->applyCasts($itemData))
                ->map(fn (array $casted) => new Snapshot($casted));
        }

        return $data;
    }
}
