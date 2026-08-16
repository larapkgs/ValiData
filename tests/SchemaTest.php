<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use LaraPkgs\ValiData\Properties\PropertyBuilder;
use LaraPkgs\ValiData\Schema;
use LaraPkgs\Validation\ValidatableCollection;

beforeEach(function () {
    $this->helpers = new class
    {
        public function getItems(Schema $subject): ?Collection
        {
            return Closure::bind(static fn (Schema $schema) => $schema->items, null, $subject)($subject);
        }
    };
});

it('accepts a variadic list of Properties on instantiation', function () {
    $schema = new Schema(
        PropertyBuilder::make('property')
    );

    expect($schema)->toBeInstanceOf(Schema::class);
});

it('deep clones', function () {
    $schema = Schema::make(
        $property = PropertyBuilder::make('property')
    );

    $cloned = clone $schema;

    expect($cloned)->not->toBe($schema)
        ->and($cloned->getItems()->get('property'))
        ->toEqual($property)
        ->not->toBe($property);

});

describe('Schema::make()', function () {
    it('provides a factory method that accepts a variadic list of Properties', function () {
        $schema = Schema::make(
            PropertyBuilder::make('property')
        );

        expect($schema)->toBeInstanceOf(Schema::class);
    });
});

describe('Schema::getItems()', function () {
    it('provides a new instance of the underlying collection of items', function () {
        $schema = Schema::make(
            $property = PropertyBuilder::make('property')
        );

        $items = $schema->getItems();

        expect($items)
            ->toBeInstanceOf(Collection::class)
            ->not->toBe($this->helpers->getItems($schema))
            ->and($items->get('property'))->not->toBe($property);
    });
});

describe('Schema::add()', function () {
    it('allows adding a variadic list of Properties after instantiation', function () {
        $schema = Schema::make(
            PropertyBuilder::make('property1')
        );

        $updated = $schema->add(
            PropertyBuilder::make('property2')
        );

        expect($updated)->toHaveCount(2)
            ->not->toBe($schema);
    });
});

describe('Schema::all()', function () {
    it('provides an array of cloned Property instances', function () {
        $schema = Schema::make(
            $property = PropertyBuilder::make('property')
        );

        $array = $schema->all();

        expect($array)
            ->toHaveKeys(['property'])
            ->and($array['property'])
            ->toEqual($property)
            ->not->toBe($property);
    });
});

describe('Schema::getValidation()', function () {
    it('provides an instance of ValidatableCollection generated on the underlying Properties', function () {
        $schema = Schema::make(
            PropertyBuilder::make('property')
        );

        expect($schema->getValidation())->toBeInstanceOf(ValidatableCollection::class);
    });
});

describe('Schema::applyDefaults', function () {
    it('applies defaults on the payload', function () {
        $schema = Schema::make(
            PropertyBuilder::make('string')->default('value'),
            PropertyBuilder::make('integer')->default(123),
            PropertyBuilder::make('defaultless')
        );

        expect($schema->applyDefaults([]))->toBe([
            'string' => 'value',
            'integer' => 123,
        ])->not->toHaveKey('defaultless');
    });
});

describe('Schema::applyPayload', function () {
    it('applies the payload to the data', function () {
        $schema = Schema::make(
            PropertyBuilder::make('property1'),
            PropertyBuilder::make('property2')
        );

        $payload = [
            'property1' => 'value1',
            'property2' => 'value2',
            'property3' => 'value3',
        ];

        $data = $schema->applyPayload($payload);

        expect($data)->toBe([
            'property1' => 'value1',
            'property2' => 'value2',
        ]);
    });
});

describe('Countable implementation', function () {
    it('implements the Countable interface', function () {
        expect(Schema::make())->toBeInstanceOf(Countable::class);
    });

    describe('State::count()', function () {
        it('counts the number of properties in the collection', function () {
            $schema = Schema::make();

            expect($schema->count())->toBe(0)
                ->and(count($schema))->toBe(0);

            $updated = $schema->add(
                PropertyBuilder::make('property1'),
                PropertyBuilder::make('property2')
            );

            expect($updated->count())->toBe(2)
                ->and(count($updated))->toBe(2);
        });
    });
});

describe('IteratorAggregate implementation', function () {
    it('implements the IteratorAggregate interface', function () {
        expect(Schema::make())->toBeInstanceOf(IteratorAggregate::class);
    });

    describe('State::getIterator()', function () {
        it('makes the collection traversable', function () {
            expect(Schema::make()->getIterator())->toBeInstanceOf(ArrayIterator::class);
        });
    });
});
