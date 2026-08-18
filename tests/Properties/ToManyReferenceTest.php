<?php

use Illuminate\Support\Collection;
use LaraPkgs\ValiData\Contracts\Property;
use LaraPkgs\ValiData\Properties\PropertyBuilder;
use LaraPkgs\ValiData\Properties\ToManyReference;
use LaraPkgs\ValiData\SchemaBuilder;
use LaraPkgs\ValiData\Snapshot;
use LaraPkgs\Validation\ValidatableCollection;

beforeEach(function () {
    $this->property = new ToManyReference('toManyReference', new SchemaBuilder(
        new PropertyBuilder('name')
            ->default('admin')
            ->required(),
        new PropertyBuilder('email')->required()
    ));
});

it('expects a name and schema on instantiation', function () {
    expect($this->property)
        ->toBeInstanceOf(ToManyReference::class)
        ->toBeInstanceOf(Property::class);
});

describe('ToManyReference::getName()', function () {
    it('provides the name of the property', function () {
        expect($this->property->getName())
            ->toBe('toManyReference');
    });
});

describe('ToManyReference::applyDefault()', function () {
    it('delegates to Schema::applyDefault()', function () {
        $payload = [
            'toManyReference' => [
                [],
                ['name' => 'John Doe'],
            ],
        ];

        $payload = $this->property->applyDefault($payload);

        expect($payload)->toBe([
            'toManyReference' => [
                ['name' => 'admin'],
                ['name' => 'John Doe'],
            ],
        ]);
    });
});

describe('ToManyReference::applyPayload()', function () {
    it('delegates to Schema::applyPayload()', function () {
        $payload = [
            'toManyReference' => [
                [
                    'name' => 'Johanna Doe',
                    'email' => 'mail@johannadoe.com',
                ],
                [
                    'name' => 'John Doe',
                    'email' => 'mail@johndoe.com',
                ],
            ],
        ];

        $data = $this->property->applyPayload($payload, []);

        expect($data)->toBe($payload);
    });
});

describe('ToManyReference::getValidation()', function () {
    it('delegates to Schema::getValidatableCollection()', function () {
        $validatable = $this->property->getValidation();

        expect($validatable)
            ->toBeInstanceOf(ValidatableCollection::class)
            ->and($validatable->toValidatorArguments()['rules'])
            ->toBe([
                'toManyReference.*.name' => ['required'],
                'toManyReference.*.email' => ['required'],
            ]);
    });
});

describe('ToManyReference::applyCast()', function () {
    it('delegates to Schema::applyCast()', function () {
        $data = [
            'toManyReference' => [
                [
                    'name' => 'Johanna Doe',
                    'email' => 'mail@johannadoe.com',
                ],
                [
                    'name' => 'John Doe',
                    'email' => 'mail@johndoe.com',
                ],
            ],
        ];

        $casted = $this->property->applyCast($data);

        expect($casted['toManyReference'])
            ->toBeInstanceOf(Collection::class)
            ->each(fn($value, $key) => $value
                ->toBeInstanceof(Snapshot::class)
                ->all()->toBe($data['toManyReference'][$key])
            );
    });
});
