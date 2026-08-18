<?php

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;
use LaraPkgs\ValiData\Properties\PropertyBuilder;
use LaraPkgs\ValiData\SchemaBuilder;
use LaraPkgs\ValiData\Snapshot;

it('expects an payload array on instantiation', function () {
    $data = new Snapshot(['property' => 'value']);

    expect($data)->toBeInstanceOf(Snapshot::class);
});

it('allows an optional schema on instantiation', function () {
    $schema = new SchemaBuilder(
        PropertyBuilder::make('property')
    );

    $data = new Snapshot(['property' => 'value'], $schema);

    expect($data)->toBeInstanceOf(Snapshot::class);
});

it('applies property defaults when a schema is provided', function () {
    $schema = new SchemaBuilder(
        PropertyBuilder::make('property')->default('value')
    );

    $data = new Snapshot([], $schema);
    expect($data->all())->toBe(['property' => 'value']);

    $data = new Snapshot(['property' => 'provided value'], $schema);
    expect($data->all())->toBe(['property' => 'provided value']);
});

it('validates the data when a schema is provided', function () {
    $schema = new SchemaBuilder(
        PropertyBuilder::make('property')->required()
    );

    $payload = ['property' => 'value'];
    $data = new Snapshot($payload, $schema);
    expect($data->all())->toBe($payload);

    expect(fn () => new Snapshot([], $schema))
        ->toThrow(ValidationException::class);
});

it('implements the magic __get() method that delegates to the get() method', function () {
    $data = new Snapshot(['property' => 'value']);

    expect($data->property)->toBe('value');
});

it('implements the magic __isset() method that delegates to the has() method', function () {
    $data = new Snapshot([]);
    expect(isset($data->property))->toBeFalse();

    $data = new Snapshot(['property' => 'value']);
    expect(isset($data->property))->toBeTrue();
});

describe('Snapshot::get()', function () {
    it('provides the value of a given property', function () {
        $data = new Snapshot(['property' => 'value']);

        expect($data->get('property'))->toBe('value');
    });

    it('throws an exception whe trying to get the value of a non existing property', function () {
        $data = new Snapshot([]);

        expect(fn () => $data->get('property'))
            ->toThrow(Exception::class);
    });
});

describe('Snapshot::has()', function () {
    it('indicates if a given property exists', function () {
        $data = new Snapshot([]);
        expect($data->has('property'))->toBeFalse();

        $data = new Snapshot(['property' => 'value']);
        expect($data->has('property'))->toBeTrue();
    });
});

describe('Snapshot::all()', function () {
    it('provides an array of all properties', function () {
        $data = new Snapshot(['property' => 'value']);

        expect($data->all())->toBe(['property' => 'value']);
    });

    it('clones property values when they are an object', function () {
        $data = new Snapshot([
            'property1' => 'value',
            'property2' => $object = new stdClass,
        ]);

        expect($data->all()['property2'])
            ->not->toBe($object)
            ->toEqual($object);
    });
});

describe('Snapshot::toCollection()', function () {
    it('provides an collection of all properties', function () {
        $data = new Snapshot(['property' => 'value']);

        expect($data->toCollection())
            ->toBeInstanceOf(Collection::class)
            ->all()->toBe(['property' => 'value']);
    });

    it('clones property values when they are an object', function () {
        $data = new Snapshot([
            'property1' => 'value',
            'property2' => $object = new stdClass,
        ]);

        expect($data->toCollection())
            ->get('property1')->toBe('value')
            ->get('property2')
            ->not->toBe($object)
            ->toEqual($object);
    });
});

describe('Arrayable implementation', function () {
    it('implements the Countable interface', function () {
        expect(new Snapshot([]))->toBeInstanceOf(Arrayable::class);
    });

    describe('Snapshot::toArray()', function () {
        it('serializes property object values that implement Arrayable', function () {
            $value = new class implements Arrayable
            {
                public function toArray(): array
                {
                    return ['nested' => 'value'];
                }
            };

            $data = new Snapshot(['property' => $value]);

            expect($data->toArray())->toBe([
                'property' => [
                    'nested' => 'value',
                ],
            ]);
        });

        it('serializes property object values that implement JsonSerializable', function () {
            $value = new class implements JsonSerializable
            {
                public function jsonSerialize(): mixed
                {
                    return ['nested' => 'value'];
                }
            };

            $data = new Snapshot(['property' => $value]);

            expect($data->toArray())->toBe([
                'property' => [
                    'nested' => 'value',
                ],
            ]);
        });

        it('serializes property object values that implement Jsonable', function () {
            $value = new class implements Jsonable
            {
                public function toJson($options = 0)
                {
                    return json_encode(['nested' => 'value']);
                }
            };

            $data = new Snapshot(['property' => $value]);

            expect($data->toArray())->toBe([
                'property' => [
                    'nested' => 'value',
                ],
            ]);
        });

        it('serializes array values', function () {
            $data = new Snapshot(['property' => ['nested' => 'value']]);

            expect($data->toArray())->toBe([
                'property' => [
                    'nested' => 'value',
                ],
            ]);
        });

        it('deep serializes array values', function () {
            $value = new class implements Jsonable
            {
                public function toJson($options = 0)
                {
                    return json_encode(['deep_nested' => 'value']);
                }
            };

            $data = new Snapshot(['property' => [
                'nested' => $value,
            ]]);

            expect($data->toArray())->toBe([
                'property' => [
                    'nested' => [
                        'deep_nested' => 'value',
                    ],
                ],
            ]);
        });

        it('leaves non serializable values untouched', function (mixed $value) {
            $data = new Snapshot(['property' => $value]);

            expect($data->toArray())->toBe(['property' => $value]);
        })->with(['abc', 123, ['abc', '123']]);
    });
});

describe('ArrayAccess implementation', function () {
    it('implements the ArrayAccess interface', function () {
        $data = new Snapshot(['property' => 'value']);

        expect($data)->toBeInstanceOf(ArrayAccess::class);
    });

    it('throws an error when an offset is not stringable', function () {
        $data = new Snapshot(['property' => 'value']);

        expect(fn () => $data->offsetGet(0))
            ->toThrow(Exception::class);
    });

    describe('Snapshot::offsetExists()', function () {
        it('indicates if a given property is set', function () {
            $data = new Snapshot([]);

            expect($data->offsetExists('property'))->toBeFalse()
                ->and(isset($data['property']))->toBeFalse();

            $data = new Snapshot(['property' => 'value']);

            expect($data->offsetExists('property'))->toBeTrue()
                ->and(isset($data['property']))->toBeTrue();
        });
    });

    describe('Snapshot::offsetGet()', function () {
        it('provides the value of a given property', function () {
            $data = new Snapshot(['property' => 'value']);

            expect($data->offsetGet('property'))->toBe('value')
                ->and($data['property'])->toBe('value');
        });
    });

    describe('Snapshot::offsetSet()', function () {
        it('throws an exception as this is a readonly object', function () {
            $data = new Snapshot([]);

            expect(fn () => $data->offsetSet('property', 'value'))
                ->toThrow(Exception::class);
        });
    });

    describe('Snapshot::offsetUnset()', function () {
        it('throws an exception as this is a readonly object', function () {
            $data = new Snapshot([]);

            expect(fn () => $data->offsetUnset('property'))
                ->toThrow(Exception::class);
        });
    });
});

describe('Countable implementation', function () {
    it('implements the Countable interface', function () {
        expect(new Snapshot([]))->toBeInstanceOf(Countable::class);
    });

    describe('Snapshot::count()', function () {
        it('counts the number of attributes in the state', function () {
            $data = new Snapshot([]);

            expect($data->count())->toBe(0)
                ->and(count($data))->toBe(0);

            $data = new Snapshot(['property1' => 'value1', 'property2' => 'value2']);

            expect($data->count())->toBe(2)
                ->and(count($data))->toBe(2);
        });
    });
});

describe('IteratorAggregate implementation', function () {
    it('implements the IteratorAggregate interface', function () {
        expect(new Snapshot([]))->toBeInstanceOf(IteratorAggregate::class);
    });

    describe('Snapshot::getIterator()', function () {
        it('makes the state traversable', function () {
            $data = new Snapshot(['property' => 'value']);

            expect($data->getIterator())->toBeInstanceOf(ArrayIterator::class);
        });
    });
});

describe('Jsonable implementation', function () {
    it('implements the Jsonable interface', function () {
        expect(new Snapshot([]))->toBeInstanceOf(Jsonable::class);
    });

    it('serializes to json', function () {
        $data = new Snapshot(['property' => ['nested' => 'value']]);

        expect($data->toJson())->json()->toBe([
            'property' => [
                'nested' => 'value',
            ],
        ]);
    });

    it('throws an exception when unable to serialize', function () {
        // Invalid UTF-8 sequence
        $value = "\xC3\x28";

        $data = new Snapshot(['property' => $value]);

        expect(fn () => $data->toJson())
            ->toThrow(JsonException::class);
    });
});

describe('JsonableSerializable implementation', function () {
    it('implements the JsonSerializable interface', function () {
        expect(new Snapshot([]))->toBeInstanceOf(JsonSerializable::class);
    });

    it('delegates to Snapshot::toArray()', function () {
        $data = new Snapshot(['property' => ['nested' => 'value']]);

        expect($data->jsonSerialize())
            ->toBe($data->toArray());
    });
});

describe('Responsable implementation', function () {
    it('implements the Responsable interface', function () {
        expect(new Snapshot([]))->toBeInstanceOf(Responsable::class);
    });

    it('serializes to a response', function () {
        $data = new Snapshot(['property' => 'value']);

        $response = $data->toResponse(Request::instance());

        expect($response)->toBeInstanceOf(JsonResponse::class)
            ->and($response->getStatusCode())->toBe(200)
            ->and($response->getData(true))->toBe(['property' => 'value']);
    });
});

describe('Stringable implementation', function () {
    it('implements the Stringable interface', function () {
        expect(new Snapshot([]))->toBeInstanceOf(Stringable::class);
    });

    describe('Snapshot::__toString()', function () {
        it('delegates to Snapshot::toJson()', function () {
            $data = new Snapshot(['property' => ['nested' => 'value']]);

            expect($data->__toString())
                ->toBe($data->toJson());
        });
    });
});
