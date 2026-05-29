<?php

use App\Models\ObjectType;
use App\Models\SightseeingObject;

test('object type supports hierarchy relationships and breadcrumbs', function () {
    $parent = ObjectType::factory()->create(['name' => 'Zabytki architektury']);
    $child = ObjectType::factory()->childOf($parent)->create(['name' => 'Zamki i fortyfikacje']);
    $grandchild = ObjectType::factory()->childOf($child)->create(['name' => 'Zamki']);

    expect($parent->children()->first()->is($child))->toBeTrue()
        ->and($child->parent->is($parent))->toBeTrue()
        ->and(collect($grandchild->breadcrumb())->pluck('name')->all())->toBe([
            'Zabytki architektury',
            'Zamki i fortyfikacje',
            'Zamki',
        ]);
});

test('object type resolves descendant ids up to three taxonomy levels', function () {
    $parent = ObjectType::factory()->create();
    $child = ObjectType::factory()->childOf($parent)->create();
    $grandchild = ObjectType::factory()->childOf($child)->create();
    $greatGrandchild = ObjectType::factory()->childOf($grandchild)->create();

    expect($parent->descendantIds()->all())->toBe([$child->id, $grandchild->id])
        ->and($parent->descendantIds()->contains($greatGrandchild->id))->toBeFalse();
});

test('object type belongs to many sightseeing objects', function () {
    $type = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->create();

    $object->objectTypes()->attach($type);

    expect($type->sightseeingObjects()->first()->is($object))->toBeTrue();
});

test('object type slugs are generated with collision handling', function () {
    $first = ObjectType::query()->create(['name' => 'Zamki']);
    $second = ObjectType::query()->create(['name' => 'Zamki']);

    expect($first->slug)->toBe('zamki')
        ->and($second->slug)->toBe('zamki-2');
});
