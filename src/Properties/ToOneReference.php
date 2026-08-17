<?php

namespace LaraPkgs\ValiData\Properties;

use LaraPkgs\ValiData\Contracts\Property;
use LaraPkgs\ValiData\Contracts\Schema;
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
        return $this->schema->applyDefaults($payload);
    }

    public function applyPayload(array $payload, array $data): array
    {
        return $this->schema->applyPayload($payload);
    }

    public function getValidation(): ValidatableCollection
    {
        return $this->schema->getValidatableCollection();
    }
}
