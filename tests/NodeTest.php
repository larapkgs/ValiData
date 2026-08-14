<?php

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use LaraPkgs\ValiData\Node;

it('expects an payload array on instantiation', function () {
    $node = new Node(['property' => 'value']);

    expect($node)->toBeInstanceOf(Node::class);
});

it('implements the magic __get() method that delegates to the get() method', function () {
    $node = new Node(['property' => 'value']);

    expect($node->property)->toBe('value');
});

it('implements the magic __isset() method that delegates to the has() method', function () {
    $node = new Node([]);
    expect(isset($node->property))->toBeFalse();

    $node = new Node(['property' => 'value']);
    expect(isset($node->property))->toBeTrue();
});

describe('Node::get()', function () {
    it('provides the value of a given property', function () {
        $node = new Node(['property' => 'value']);

        expect($node->get('property'))->toBe('value');
    });

    it('throws an exception whe trying to get the value of a non existing property', function () {
        $node = new Node([]);

        expect(fn () => $node->get('property'))
            ->toThrow(Exception::class);
    });
});

describe('Node::has()', function () {
    it('indicates if a given property exists', function () {
        $node = new Node([]);
        expect($node->has('property'))->toBeFalse();

        $node = new Node(['property' => 'value']);
        expect($node->has('property'))->toBeTrue();
    });
});

describe('Node::all()', function () {
    it('provides an array of all properties', function () {
        $node = new Node(['property' => 'value']);

        expect($node->all())->toBe(['property' => 'value']);
    });
});

describe('Arrayable implementation', function () {
    it('implements the Countable interface', function () {
        expect(new Node([]))->toBeInstanceOf(Arrayable::class);
    });

    describe('Node::toArray()', function () {
        it('serializes property object values that implement Arrayable', function () {
            $value = new class implements Arrayable
            {
                public function toArray(): array
                {
                    return ['nested' => 'value'];
                }
            };

            $node = new Node(['property' => $value]);

            expect($node->toArray())->toBe([
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

            $node = new Node(['property' => $value]);

            expect($node->toArray())->toBe([
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

            $node = new Node(['property' => $value]);

            expect($node->toArray())->toBe([
                'property' => [
                    'nested' => 'value',
                ],
            ]);
        });

        it('serializes array values', function () {
            $node = new Node(['property' => ['nested' => 'value']]);

            expect($node->toArray())->toBe([
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

            $node = new Node(['property' => [
                'nested' => $value,
            ]]);

            expect($node->toArray())->toBe([
                'property' => [
                    'nested' => [
                        'deep_nested' => 'value',
                    ],
                ],
            ]);
        });

        it('leaves non serializable values untouched', function (mixed $value) {
            $node = new Node(['property' => $value]);

            expect($node->toArray())->toBe(['property' => $value]);
        })->with(['abc', 123, ['abc', '123'], new stdClass]);
    });
});

describe('ArrayAccess implementation', function () {
    it('implements the ArrayAccess interface', function () {
        $node = new Node(['property' => 'value']);

        expect($node)->toBeInstanceOf(ArrayAccess::class);
    });

    it('throws an error when an offset is not stringable', function () {
        $node = new Node(['property' => 'value']);

        expect(fn () => $node->offsetGet(0))
            ->toThrow(Exception::class);
    });

    describe('Node::offsetExists()', function () {
        it('indicates if a given property is set', function () {
            $node = new Node([]);

            expect($node->offsetExists('property'))->toBeFalse()
                ->and(isset($node['property']))->toBeFalse();

            $node = new Node(['property' => 'value']);

            expect($node->offsetExists('property'))->toBeTrue()
                ->and(isset($node['property']))->toBeTrue();
        });
    });

    describe('Node::offsetGet()', function () {
        it('provides the value of a given property', function () {
            $node = new Node(['property' => 'value']);

            expect($node->offsetGet('property'))->toBe('value')
                ->and($node['property'])->toBe('value');
        });
    });

    describe('Node::offsetSet()', function () {
        it('throws an exception as this is a readonly object', function () {
            $node = new Node([]);

            expect(fn () => $node->offsetSet('property', 'value'))
                ->toThrow(Exception::class);
        });
    });

    describe('Node::offsetUnset()', function () {
        it('throws an exception as this is a readonly object', function () {
            $node = new Node([]);

            expect(fn () => $node->offsetUnset('property'))
                ->toThrow(Exception::class);
        });
    });
});

describe('Countable implementation', function () {
    it('implements the Countable interface', function () {
        expect(new Node([]))->toBeInstanceOf(Countable::class);
    });

    describe('Node::count()', function () {
        it('counts the number of attributes in the state', function () {
            $node = new Node([]);

            expect($node->count())->toBe(0)
                ->and(count($node))->toBe(0);

            $node = new Node(['property1' => 'value1', 'property2' => 'value2']);

            expect($node->count())->toBe(2)
                ->and(count($node))->toBe(2);
        });
    });
});

describe('IteratorAggregate implementation', function () {
    it('implements the IteratorAggregate interface', function () {
        expect(new Node([]))->toBeInstanceOf(IteratorAggregate::class);
    });

    describe('Node::getIterator()', function () {
        it('makes the state traversable', function () {
            $node = new Node(['property' => 'value']);

            expect($node->getIterator())->toBeInstanceOf(ArrayIterator::class);
        });
    });
});

describe('Jsonable implementation', function () {
    it('implements the Jsonable interface', function () {
        expect(new Node([]))->toBeInstanceOf(Jsonable::class);
    });

    it('serializes to json', function () {
        $node = new Node(['property' => ['nested' => 'value']]);

        expect($node->toJson())->json()->toBe([
            'property' => [
                'nested' => 'value',
            ],
        ]);
    });

    it('throws an exception when unable to serialize', function () {
        // Invalid UTF-8 sequence
        $value = "\xC3\x28";

        $node = new Node(['property' => $value]);

        expect(fn () => $node->toJson())
            ->toThrow(JsonException::class);
    });
});

describe('JsonableSerializable implementation', function () {
    it('implements the JsonSerializable interface', function () {
        expect(new Node([]))->toBeInstanceOf(JsonSerializable::class);
    });

    it('delegates to Node::toArray()', function () {
        $node = new Node(['property' => ['nested' => 'value']]);

        expect($node->jsonSerialize())
            ->toBe($node->toArray());
    });
});

describe('Responsable implementation', function () {
    it('implements the Responsable interface', function () {
        expect(new Node([]))->toBeInstanceOf(Responsable::class);
    });

    it('serializes to a response', function () {
        $node = new Node(['property' => 'value']);

        $response = $node->toResponse(Request::instance());

        expect($response)->toBeInstanceOf(JsonResponse::class)
            ->and($response->getStatusCode())->toBe(200)
            ->and($response->getData(true))->toBe(['property' => 'value']);
    });
});

describe('Stringable implementation', function () {
    it('implements the Stringable interface', function () {
        expect(new Node([]))->toBeInstanceOf(Stringable::class);
    });

    describe('Node::__toString()', function () {
        it('delegates to Node::toJson()', function () {
            $node = new Node(['property' => ['nested' => 'value']]);

            expect($node->__toString())
                ->toBe($node->toJson());
        });
    });
});
