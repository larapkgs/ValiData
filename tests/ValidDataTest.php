<?php

use LaraPkgs\ValiData\Concerns\HasValidData;
use LaraPkgs\ValiData\Contracts\Schema;
use LaraPkgs\ValiData\Properties\PropertyBuilder;
use LaraPkgs\ValiData\SchemaBuilder;
use LaraPkgs\ValiData\ValidData;

beforeEach(function () {
    $this->helpers = new class
    {
        public function makeValidData(array $payload): ValidData
        {
            return new class($payload) extends ValidData
            {
                protected function makeSchema(): Schema
                {
                    return SchemaBuilder::make(
                        new PropertyBuilder('property')->required()
                    );
                }
            };
        }
    };
});

it('uses the HasValidData trait', function () {
    $class = $this->helpers->makeValidData(['property' => 'value']);

    expect(class_uses_recursive($class))->toHaveKey(HasValidData::class);
});

it('delegates instantiation to HasValidData::processPayload()', function () {
    $payload = ['property' => 'value'];

    $class = $this->helpers->makeValidData($payload);

    expect($class->all())->toBe($payload);
});
