<?php

use Illuminate\Support\Collection;
use LaraPkgs\ValiData\Contracts\Schema as SchemaContract;
use LaraPkgs\ValiData\Properties\PropertyBuilder;
use LaraPkgs\ValiData\Schema;
use LaraPkgs\Validation\ValidatableCollection;

beforeEach(function () {
    $this->helpers = new class
    {
        public function makeSchema(?array $definition = null): Schema
        {
            $definition ??= [PropertyBuilder::make('property')];

            return new class($definition) extends Schema
            {
                public function __construct(
                    protected array $definition
                ) {}

                protected function definition(): array
                {
                    return $this->definition;
                }
            };
        }

        public function getProperties(Schema $subject): ?Collection
        {
            return Closure::bind(static fn (Schema $schema) => $schema->properties, null, $subject)($subject);
        }
    };
});

it('implements the Schema interface', function () {
    expect($this->helpers->makeSchema())->toBeInstanceOf(SchemaContract::class);
});

it('defaults to an empty schema', function () {
    $schema = new class extends Schema {};

    expect($schema->getProperties())->toHaveCount(0);
});

it('deep clones', function () {
    $schema = $this->helpers->makeSchema([
        $property = PropertyBuilder::make('property'),
    ]);

    $cloned = clone $schema;

    expect($cloned)->not->toBe($schema)
        ->and($cloned->getProperties()->get('property'))
        ->toEqual($property)
        ->not->toBe($property);

});

describe('Schema::getProperties()', function () {
    it('provides a new instance of the underlying collection of properties', function () {
        $schema = $this->helpers->makeSchema([
            $property = PropertyBuilder::make('property'),
        ]);

        $properties = $schema->getProperties();

        expect($properties)
            ->toBeInstanceOf(Collection::class)
            ->not->toBe($this->helpers->getProperties($schema))
            ->and($properties->get('property'))->not->toBe($property);
    });
});

describe('Schema::all()', function () {
    it('provides an array of cloned Property instances', function () {
        $schema = $this->helpers->makeSchema([
            $property = PropertyBuilder::make('property'),
        ]);

        $array = $schema->all();

        expect($array)
            ->toHaveKeys(['property'])
            ->and($array['property'])
            ->toEqual($property)
            ->not->toBe($property);
    });
});

describe('Schema::getValidatableCollection()', function () {
    it('provides an instance of ValidatableCollection generated on the underlying Properties', function () {
        $schema = $this->helpers->makeSchema();

        expect($schema->getValidatableCollection())->toBeInstanceOf(ValidatableCollection::class);
    });
});

describe('Schema::applyDefaults', function () {
    it('applies defaults on the payload', function () {
        $schema = $this->helpers->makeSchema([
            PropertyBuilder::make('string')->default('value'),
            PropertyBuilder::make('integer')->default(123),
            PropertyBuilder::make('defaultless'),
        ]);

        expect($schema->applyDefaults([]))->toBe([
            'string' => 'value',
            'integer' => 123,
        ])->not->toHaveKey('defaultless');
    });
});

describe('Schema::applyPayload', function () {
    it('applies the payload to the data', function () {
        $schema = $this->helpers->makeSchema([
            PropertyBuilder::make('property1'),
            PropertyBuilder::make('property2'),
        ]);

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

describe('Schema::applyCasts()', function () {
    it('casts the data', function () {
        $schema = $this->helpers->makeSchema([
            PropertyBuilder::make('property1'),
            PropertyBuilder::make('property2'),
        ]);

        $data = [
            'property1' => 'value1',
            'property2' => 'value2',
        ];

        $casted = $schema->applyCasts($data);

        expect($casted)->toBe([
            'property1' => 'value1',
            'property2' => 'value2',
        ]);
    });
});

describe('Countable implementation', function () {
    it('implements the Countable interface', function () {
        expect($this->helpers->makeSchema())->toBeInstanceOf(Countable::class);
    });

    describe('State::count()', function () {
        it('counts the number of properties in the collection', function () {
            $schema = $this->helpers->makeSchema([]);

            expect($schema->count())->toBe(0)
                ->and(count($schema))->toBe(0);

            $schema = $this->helpers->makeSchema([
                PropertyBuilder::make('property1'),
                PropertyBuilder::make('property2'),
            ]);

            expect($schema->count())->toBe(2)
                ->and(count($schema))->toBe(2);
        });
    });
});

describe('IteratorAggregate implementation', function () {
    it('implements the IteratorAggregate interface', function () {
        expect($this->helpers->makeSchema())->toBeInstanceOf(IteratorAggregate::class);
    });

    describe('State::getIterator()', function () {
        it('makes the collection traversable', function () {
            expect($this->helpers->makeSchema()->getIterator())->toBeInstanceOf(ArrayIterator::class);
        });
    });
});
