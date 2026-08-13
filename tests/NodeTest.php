<?php

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
