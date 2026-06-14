<?php

use App\Models\ObjectType;
use App\Models\SightseeingObject;

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
