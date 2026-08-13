<?php

declare(strict_types=1);

namespace LaraPkgs\ValiData\Properties;

use Closure;

use Illuminate\Support\Facades\App;

use LaraPkgs\ValiData\Contracts\Property;
use LaraPkgs\Validation\Concerns\HasFluentRules;
use LaraPkgs\Validation\Contracts\ProvidesValidatableCollection;
use LaraPkgs\Validation\Contracts\RuleFactory;
use LaraPkgs\Validation\Contracts\ValidationRule;
use LaraPkgs\Validation\ValidatableBuilder;
use LaraPkgs\Validation\ValidatableCollection;
use ReflectionProperty;

final class PropertyBuilder implements Property
{
    use HasFluentRules;

    protected string $name;

    protected mixed $default;

    protected ?ValidatableCollection $validation = null;

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

    protected function newInstance(?Closure $callback = null): self
    {
        $instance = clone $this;

        return $callback !== null ? tap($instance, $callback) : $instance;
    }

    // Name
    public function getName(): string
    {
        return $this->name;
    }

    // Defaults
    public function default(mixed $value): self
    {
        return $this->newInstance(function (self $instance) use ($value) {
            $instance->default = $value;
        });
    }

    public function hasDefaultValue(): bool
    {
        return new ReflectionProperty($this, 'default')->isInitialized($this);
    }

    public function getDefaultValue(): mixed
    {
        return $this->default;
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

    public function withValidation(Closure $callback): self
    {
        return $this->newInstance(function (self $instance) use ($callback) {
            $instance->validation = $callback($this->resolveValidation());
        });
    }

    public function getValidation(): ValidatableCollection
    {
        return clone $this->resolveValidation();
    }

    protected function resolveValidation(): ValidatableCollection
    {
        return $this->validation ??= ValidatableCollection::make(
            ValidatableBuilder::make($this->name)
        );
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    protected function applyFluentRule(ValidationRule|string $rule, array $arguments = []): self
    {
        return $this->newInstance(function (self $instance) use ($rule, $arguments) {
            $rule = is_string($rule)
                ? $this->resolveRuleFactory()->make($rule, $arguments)
                : clone $rule;

            /** @var ValidatableBuilder $builder */
            $builder = $instance->resolveValidation()->getItems()->get($this->getName());

            $applied = $builder->applyRule($rule);

            $instance->validation = $instance->resolveValidation()->add($applied);
        });
    }

    protected function resolveRuleFactory(): RuleFactory
    {
        return App::make(RuleFactory::class);
    }
}
