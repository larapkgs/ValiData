<?php

namespace LaraPkgs\ValiData\Properties;

use LaraPkgs\ValiData\Contracts\Property;
use LaraPkgs\ValiData\Contracts\Schema;
use LaraPkgs\ValiData\Properties\Concerns\BaseProperty;
use LaraPkgs\ValiData\Snapshot;
use LaraPkgs\Validation\ValidatableBuilder;
use LaraPkgs\Validation\ValidatableCollection;

class ToOneReference implements Property
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
        /** @var array<array-key, mixed> $propertyPayload */
        $propertyPayload = $payload[$key = $this->getName()] ?? [];

        $payload[$key] = $this->schema->applyDefaults($propertyPayload);

        return $payload;
    }

    public function applyPayload(array $payload, array $data): array
    {
        /** @var array<array-key, mixed> $propertyPayload */
        $propertyPayload = $payload[$key = $this->getName()];

        $data[$key] = $this->schema->applyPayload($propertyPayload);

        return $data;
    }

    public function getValidation(): ValidatableCollection
    {
        $validation = clone $this->resolveValidation();

        $childValidation = $this->schema->getValidatableCollection()
            ->prefix($this->getName());

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
            /** @var array<array-key, mixed> $propertyData */
            $propertyData = $data[$key];

            $casted = $this->schema->applyCasts($propertyData);

            $data[$key] = new Snapshot($casted);
        }

        return $data;
    }
}
