<?php

use LaraPkgs\ValiData\Contracts\Property;
use LaraPkgs\ValiData\Properties\PropertyBuilder;
use LaraPkgs\ValiData\Properties\ToOneReference;
use LaraPkgs\ValiData\SchemaBuilder;
use LaraPkgs\Validation\ValidatableCollection;

beforeEach(function () {
    $this->property = $property = new ToOneReference('toOneReference', new SchemaBuilder(
        new PropertyBuilder('name')
            ->default('admin')
            ->required(),
        new PropertyBuilder('email')->required()
    ));
});

it('expects a name and schema on instantiation', function () {
    expect($this->property)
        ->toBeInstanceOf(ToOneReference::class)
        ->toBeInstanceOf(Property::class);
});

describe('ToOneReference::getName()', function () {
    it('provides the name of the property', function () {
        expect($this->property->getName())
            ->toBe('toOneReference');
    });
});

describe('ToOneReference::applyDefault()', function () {
    it('delegates to Schema::applyDefault()', function () {
        $payload = [];

        $payload = $this->property->applyDefault($payload);

        expect($payload)->toBe(['name' => 'admin']);
    });
});

describe('ToOneReference::applyPayload()', function () {
    it('delegates to Schema::applyPayload()', function () {
        $payload = [
            'name' => 'John Doe',
            'email' => 'mail@johndoe.com',
        ];

        $data = $this->property->applyPayload($payload, []);

        expect($data)->toBe($payload);
    });
});

describe('ToOneReference::getValidation()', function () {
    it('delegates to Schema::getValidatableCollection()', function () {
        $validatable = $this->property->getValidation();

        expect($validatable)->toBeInstanceOf(ValidatableCollection::class);
    });
});
