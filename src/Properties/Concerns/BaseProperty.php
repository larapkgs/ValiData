<?php

namespace LaraPkgs\ValiData\Properties\Concerns;

use Closure;

trait BaseProperty
{
    use InteractsWithValidation;

    protected string $name;

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
}
