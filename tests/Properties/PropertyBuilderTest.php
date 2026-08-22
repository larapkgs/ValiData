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
        public function getProtectedProperty(PropertyBuilder $subject, string $property): mixed
        {
            return Closure::bind(static fn (PropertyBuilder $class) => $class->{$property}, null, $subject)($subject);
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

        expect(class_uses_recursive($property))->toHaveKey(HasFluentRules::class);
    });

    it('provides fluent methods to add validation rules', function () {
        $property = new PropertyBuilder('property');
        expect($this->helpers->getProtectedProperty($property, 'validation'))->toBeNull();

        $property = $property->required();
        expect($this->helpers->getProtectedProperty($property, 'validation'))->toBeInstanceOf(ValidatableCollection::class);

        $newInstance = $property->min(10)->max(20)->applyRule(Rule::string());
        expect($newInstance)->not->toBe($property);

        expect($this->helpers->getProtectedProperty($newInstance, 'validation'))
            ->toBeInstanceOf(ValidatableCollection::class)
            ->not->toBe($this->helpers->getProtectedProperty($property, 'validation'));
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

    it('allows closures for custom logic', function () {
        $property = PropertyBuilder::make('property')
            ->default(function ($property, $payload) {
                $key = $property->getName();

                return tap($payload, fn (&$payload) => $payload[$key] = $key . ' default');
            });

        expect($property->applyDefault([]))->toBe(['property' => 'property default']);
    });

    it('leaves the property value untouched when a value is set on the payload', function () {
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

    it('uses custom hydration logic when set', function () {
        $property = PropertyBuilder::make('property')
            ->applyPayloadUsing(function ($property, array $data, array $payload) {
                $data[$property->getName()] = (object) $payload['property'];

                return $data;
            });

        $payload = ['property' => 'value'];
        $data = [];

        expect($property->applyPayload($payload, $data)['property'])
            ->toBeInstanceOf(StdClass::class);
    });

    it('leaves the data untouched when no value is set for the property', function () {
        $property = PropertyBuilder::make('property');

        $data = ['property' => 'value'];
        $payload = [];

        $data = $property->applyPayload($payload, $data);

        expect($data)->toBe($data);
    });
});

describe('PropertyBuilder::applyPayloadUsing()', function () {
    it('allows to set custom hydration logic', function () {
        $property = PropertyBuilder::make('property');

        expect($this->helpers->getProtectedProperty($property, 'applyPayloadUsing'))->toBeNull();

        $callback = fn () => true;
        $updated = $property->applyPayloadUsing($callback);

        expect($updated)
            ->toBeInstanceOf(PropertyBuilder::class)
            ->not->toBe($property);

        expect($this->helpers->getProtectedProperty($updated, 'applyPayloadUsing'))->toBe($callback);
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
            ->not->toBe($this->helpers->getProtectedProperty($property, 'validation'));
    });
});

describe('PropertyBuilder::applyCast()', function () {
    it('leaves the data untouched by default', function () {
        $property = PropertyBuilder::make('property');

        $data = ['property' => 'value'];

        expect($property->applyCast($data))->toBe($data);
    });

    it('uses custom casting logic when set', function () {
        $property = PropertyBuilder::make('property')
            ->applyCastUsing(function ($property, array $data) {
                $data[$property->getName()] = (object) $data['property'];

                return $data;
            });

        $data = ['property' => 'value'];

        expect($property->applyCast($data)['property'])
            ->toBeInstanceOf(StdClass::class);
    });

});

describe('PropertyBuilder::applyCastUsing()', function () {
    it('allows to set custom casting logic', function () {
        $property = PropertyBuilder::make('property');

        expect($this->helpers->getProtectedProperty($property, 'applyCastUsing'))->toBeNull();

        $callback = fn () => true;
        $updated = $property->applyCastUsing($callback);

        expect($updated)
            ->toBeInstanceOf(PropertyBuilder::class)
            ->not->toBe($property);

        expect($this->helpers->getProtectedProperty($updated, 'applyCastUsing'))->toBe($callback);
    });
});
