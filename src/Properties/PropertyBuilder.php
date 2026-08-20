<?php

declare(strict_types=1);

namespace LaraPkgs\ValiData\Properties;

use Closure;
use Illuminate\Support\Facades\App;
use LaraPkgs\ValiData\Contracts\Property;
use LaraPkgs\ValiData\Properties\Concerns\BaseProperty;
use LaraPkgs\Validation\Contracts\ProvidesValidatableCollection;
use LaraPkgs\Validation\ValidatableBuilder;
use LaraPkgs\Validation\ValidatableCollection;
use ReflectionProperty;

final class PropertyBuilder implements Property
{
    use BaseProperty;

    protected ?Closure $applyCastUsing = null;

    protected ?Closure $applyPayloadUsing = null;

    protected mixed $default;

    public static function make(string $name): self
    {
        return App::make(self::class, compact('name'));
    }

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __clone()
    {
        if ($this->validation !== null) {
            $this->validation = clone $this->validation;
        }
    }

    // Defaults
    public function default(mixed $value): self
    {
        return $this->newInstance(function (self $instance) use ($value) {
            $instance->default = $value;
        });
    }

    protected function hasDefaultValue(): bool
    {
        return new ReflectionProperty($this, 'default')->isInitialized($this);
    }

    public function applyDefault(array $payload): array
    {
        if (! array_key_exists($key = $this->getName(), $payload) && $this->hasDefaultValue()) {
            $payload[$key] = $this->default;
        }

        return $payload;
    }

    public function applyPayload(array $payload, array $data): array
    {
        if ($this->applyPayloadUsing !== null) {
            /** @var array<array-key, mixed> $data */
            $data = App::call($this->applyPayloadUsing, ['property' => $this, ...compact('payload', 'data')]);

            return $data;
        }

        if (array_key_exists($key = $this->getName(), $payload)) {
            $data[$key] = $payload[$key];
        }

        return $data;
    }

    public function applyPayloadUsing(Closure $callback): self
    {
        return $this->newInstance(function (self $instance) use ($callback) {
            $instance->applyPayloadUsing = $callback;
        });
    }

    // Validation
    public function validateUsing(ValidatableCollection|ProvidesValidatableCollection|ValidatableBuilder $validation): self
    {
        $validation = match (true) {
            $validation instanceof ProvidesValidatableCollection => $validation->getValidatableCollection(),
            $validation instanceof ValidatableBuilder => ValidatableCollection::make($validation),
            default => $validation
        };

        return $this->newInstance(function (self $instance) use ($validation) {
            $instance->validation = $validation;
        });
    }

    public function getValidation(): ValidatableCollection
    {
        return clone $this->resolveValidation();
    }

    public function makeValidation(): ValidatableCollection
    {
        return ValidatableCollection::make(
            ValidatableBuilder::make($this->name)
        );
    }

    public function applyCast(array $data): array
    {
        if ($this->applyCastUsing !== null) {
            /** @var array<array-key, mixed> $data */
            $data = App::call($this->applyCastUsing, ['property' => $this, 'data' => $data]);
        }

        return $data;
    }

    public function applyCastUsing(Closure $callback): self
    {
        return $this->newInstance(function (self $instance) use ($callback) {
            $instance->applyCastUsing = $callback;
        });
    }
}
