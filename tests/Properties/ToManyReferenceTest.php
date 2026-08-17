<?php

use LaraPkgs\ValiData\Contracts\Property;
use LaraPkgs\ValiData\Properties\PropertyBuilder;
use LaraPkgs\ValiData\Properties\ToManyReference;
use LaraPkgs\ValiData\SchemaBuilder;
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

describe('ToOneReference::getName()', function () {
    it('provides the name of the property', function () {
        expect($this->property->getName())
            ->toBe('toManyReference');
    });
});

describe('ToOneReference::applyDefault()', function () {
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

describe('ToOneReference::applyPayload()', function () {
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

describe('ToOneReference::getValidation()', function () {
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
