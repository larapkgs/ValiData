<?php

declare(strict_types=1);

use Illuminate\Validation\Rule;
use LaraPkgs\ValiData\Contracts\Property as PropertyContract;
use LaraPkgs\ValiData\Properties\PropertyBuilder;
use LaraPkgs\Validation\Concerns\HasFluentRules;
use LaraPkgs\Validation\Contracts\ProvidesValidatableCollection;
use LaraPkgs\Validation\ValidatableBuilder;
use LaraPkgs\Validation\ValidatableCollection;

beforeEach(function () {
    $this->helpers = new class
    {
        public function getValidation(PropertyBuilder $subject): ?ValidatableCollection
        {
            return Closure::bind(static fn (PropertyBuilder $property) => $property->validation, null, $subject)($subject);
        }
    };
});

it('expects a name on instantiation', function () {
    $property = new PropertyBuilder('property');

    expect($property)->toBeInstanceOf(PropertyBuilder::class);
});

it('implements the Property Interface', function () {
    $property = PropertyBuilder::make('property');

    expect($property)->toBeInstanceOf(PropertyContract::class);
});

describe('HasFluentRules', function () {
    it('uses the HadFluentRules trait', function () {
        $property = new PropertyBuilder('property');

        expect(class_uses($property))->toHaveKey(HasFluentRules::class);
    });

    it('provides fluent methods to add validation rules', function () {
        $property = new PropertyBuilder('property');
        expect($this->helpers->getValidation($property))->toBeNull();

        $property = $property->required();
        expect($this->helpers->getValidation($property))->toBeInstanceOf(ValidatableCollection::class);

        $newInstance = $property->min(10)->max(20)->applyRule(Rule::string());
        expect($newInstance)->not->toBe($property);

        expect($this->helpers->getValidation($newInstance))
            ->toBeInstanceOf(ValidatableCollection::class)
            ->not->toBe($this->helpers->getValidation($property));
    });
});

describe('PropertyBuilder::make()', function () {
    it('provides as factory method', function () {
        $property = PropertyBuilder::make('property');

        expect($property)->toBeInstanceOf(PropertyBuilder::class);
    });
});

describe('PropertyBuilder::getName()', function () {
    it('provides the name', function () {
        $property = PropertyBuilder::make('property');

        expect($property->getName())->toBe('property');
    });
});

describe('PropertyBuilder::default()', function () {
    it('sets a default', function () {
        $property = PropertyBuilder::make('property');

        $updated = $property->default('value');

        expect($updated)->toBeInstanceOf(PropertyContract::class)
            ->not->toBe($property);
    });
});

describe('PropertyBuilder::applyDefault', function () {
    it('applies the default value to the payload', function () {
        $property = PropertyBuilder::make('property')->default('value');

        expect($property->applyDefault([]))->toBe(['property' => 'value']);
    });

    it('leaves the property value untouched when a valua is set on the payload', function () {
        $property = PropertyBuilder::make('property')->default('value');

        $payload = ['property' => 'set value'];

        expect($property->applyDefault($payload))->toBe($payload);
    });
});

describe('PropertyBuilder::applyPayload', function () {
    it('applies the property value in the payload to the data', function () {
        $property = PropertyBuilder::make('property');

        $data = [];
        $payload = ['property' => 'value'];

        $data = $property->applyPayload($payload, $data);

        expect($data)->toBe($payload);
    });

    it('leaves the data untouched when no value is set for the property', function () {
        $property = PropertyBuilder::make('property');

        $data = ['property' => 'value'];
        $payload = [];

        $data = $property->applyPayload($payload, $data);

        expect($data)->toBe($data);
    });
});

describe('PropertyBuilder::validateUsing()', function () {
    it('allows to set/overwrite validation using a ValidatableCollection', function () {
        $property = PropertyBuilder::make('property');

        $collection = ValidatableCollection::make(
            ValidatableBuilder::make('property')->required()
        );

        $updated = $property->validateUsing($collection);

        expect($updated)->toBeInstanceOf(PropertyContract::class)
            ->not->toBe($property);

        $data = ['property' => 'value'];

        expect($updated->getValidation()->validate($data))->toBe($data);
    });

    it('allows to set/overwrite validation using an instance of ProvidesValidatableCollection', function () {
        $property = PropertyBuilder::make('property');

        $instance = new class implements ProvidesValidatableCollection
        {
            public function getValidatableCollection(): ValidatableCollection
            {
                return ValidatableCollection::make(
                    ValidatableBuilder::make('property')->required()
                );
            }
        };

        $updated = $property->validateUsing($instance);

        expect($updated)->toBeInstanceOf(PropertyContract::class)
            ->not->toBe($property);

        $data = ['property' => 'value'];

        expect($updated->getValidation()->validate($data))->toBe($data);
    });

    it('allows to set/overwrite validation using a ValidatableBuilder', function () {
        $property = PropertyBuilder::make('property');

        $builder = ValidatableBuilder::make('property')->required();

        $updated = $property->validateUsing($builder);

        expect($updated)->toBeInstanceOf(PropertyContract::class)
            ->not->toBe($property);

        $data = ['property' => 'value'];

        expect($updated->getValidation()->validate($data))->toBe($data);
    });

});

describe('PropertyBuilder::withValidation()', function () {
    it('allows to set validation for index based arrays', function () {
        $property = PropertyBuilder::make('items')->array();

        $updated = $property->withValidation(function (ValidatableCollection $validation) {
            return $validation->add(ValidatableBuilder::make('items.*')->integer());
        });

        expect($updated)->toBeInstanceOf(PropertyBuilder::class)
            ->not->toBe($property);

        $data = [
            'items' => [1, 3, -10],
        ];

        expect($updated->getValidation()->validate($data))->toBe($data);
    });

    it('allows to set validation for associative arrays', function () {
        $property = PropertyBuilder::make('person')->array();

        $updated = $property->withValidation(function (ValidatableCollection $validation) {
            return $validation->merge(
                ValidatableCollection::make(
                    ValidatableBuilder::make('name')->required()->string(),
                    ValidatableBuilder::make('age')->required()->integer()
                )->prefix('person')
            );
        });

        expect($updated)->toBeInstanceOf(PropertyBuilder::class)
            ->not->toBe($property);

        $data = [
            'person' => [
                'name' => 'John Doe',
                'age' => 123,
            ],
        ];

        expect($updated->getValidation()->validate($data))->toBe($data);
    });
});

describe('PropertyBuilder::getValidation()', function () {
    it('provides a new instance of the underlying ValidatableBuilder', function () {
        $property = PropertyBuilder::make('property')->required();

        expect($property->getValidation())
            ->toBeInstanceOf(ValidatableCollection::class)
            ->not->toBe($this->helpers->getValidation($property));
    });
});

describe('PropertyBuilder::applyCast()', function () {
    it('leaves the data untouched by default', function () {
        $property = PropertyBuilder::make('property');

        $data = ['property' => 'value'];

        expect($property->applyCast($data))->toBe($data);
    });
});
