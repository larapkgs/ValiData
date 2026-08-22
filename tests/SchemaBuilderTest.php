<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use LaraPkgs\ValiData\Properties\PropertyBuilder;
use LaraPkgs\ValiData\Schema;
use LaraPkgs\ValiData\SchemaBuilder;

beforeEach(function () {
    $this->helpers = new class
    {
        public function getItems(SchemaBuilder $subject): ?Collection
        {
            return Closure::bind(static fn (SchemaBuilder $schema) => $schema->properties, null, $subject)($subject);
        }
    };
});

it('extends the abstract Schema class', function () {
    $schema = new SchemaBuilder;

    expect($schema)->toBeInstanceOf(Schema::class);
});

it('accepts a variadic list of Properties on instantiation', function () {
    $schema = new SchemaBuilder(
        PropertyBuilder::make('property')
    );

    expect($schema)->toBeInstanceOf(SchemaBuilder::class);
});

describe('SchemaBuilder::make()', function () {
    it('provides a factory method that accepts a variadic list of Properties', function () {
        $schema = SchemaBuilder::make(
            PropertyBuilder::make('property')
        );

        expect($schema)->toBeInstanceOf(SchemaBuilder::class);
    });
});

describe('SchemaBuilder::add()', function () {
    it('allows adding a variadic list of Properties after instantiation', function () {
        $schema = SchemaBuilder::make(
            PropertyBuilder::make('property1')
        );

        $updated = $schema->add(
            PropertyBuilder::make('property2')
        );

        expect($updated)->toHaveCount(2)
            ->not->toBe($schema);
    });
});
