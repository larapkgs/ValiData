<?php

namespace LaraPkgs\ValiData\Properties;

use LaraPkgs\ValiData\Contracts\Property;
use LaraPkgs\ValiData\Contracts\Schema;
use LaraPkgs\ValiData\Snapshot;
use LaraPkgs\Validation\ValidatableCollection;

class ToOneReference implements Property
{
    protected string $name;

    protected Schema $schema;

    public function __construct(string $name, Schema $schema)
    {
        $this->name = $name;
        $this->schema = $schema;
    }

    public function getName(): string
    {
        return $this->name;
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
        return $this->schema->getValidatableCollection()
            ->prefix($this->getName());
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
