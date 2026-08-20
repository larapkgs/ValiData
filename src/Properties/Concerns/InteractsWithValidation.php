<?php

namespace LaraPkgs\ValiData\Properties\Concerns;

use Closure;
use Illuminate\Support\Facades\App;
use LaraPkgs\Validation\Concerns\HasFluentRules;
use LaraPkgs\Validation\Contracts\RuleFactory;
use LaraPkgs\Validation\Contracts\ValidationRule;
use LaraPkgs\Validation\ValidatableBuilder;
use LaraPkgs\Validation\ValidatableCollection;

trait InteractsWithValidation
{
    use HasFluentRules;

    protected ?ValidatableCollection $validation = null;

    public function withValidation(Closure $callback): self
    {
        return $this->newInstance(function (self $instance) use ($callback) {
            $instance->validation = $callback($this->resolveValidation());
        });
    }

    protected function resolveValidation(): ValidatableCollection
    {
        return $this->validation ??= $this->makeValidation();
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

    abstract protected function makeValidation(): ValidatableCollection;
}
