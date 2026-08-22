<?php

declare(strict_types=1);

namespace LaraPkgs\ValiData;

use LaraPkgs\ValiData\Contracts\Property;

final class SchemaBuilder extends Schema
{
    public static function make(Property ...$items): self
    {
        return new self(...$items);
    }

    public function __construct(Property ...$items)
    {
        $this->properties = $this->processProperties(...$items);
    }

    public function add(Property ...$items): self
    {
        return $this->newInstance(function (self $instance) use ($items) {
            $instance->properties = $instance->resolveProperties()->merge(
                $this->processProperties(...$items)
            );
        });
    }
}
