<?php

use App\Models\Locality;
use App\Models\SightseeingObject;
use App\Models\Voivodeship;

test('locality belongs to a voivodeship', function () {
    $voivodeship = Voivodeship::factory()->create();
    $locality = Locality::factory()->for($voivodeship)->create(['name' => 'Kraków']);

    expect($locality->voivodeship)->toBeInstanceOf(Voivodeship::class)
        ->and($locality->voivodeship->id)->toBe($voivodeship->id);
});

test('locality has many sightseeing objects', function () {
    $locality = Locality::factory()->create();
    $object1 = SightseeingObject::factory()->for($locality)->create();
    $object2 = SightseeingObject::factory()->for($locality)->create();

    expect($locality->sightseeingObjects)->toHaveCount(2)
        ->and($locality->sightseeingObjects->pluck('id')->all())->toBe([$object1->id, $object2->id]);
});

test('locality slugs are generated with collision handling', function () {
    $voivodeship = Voivodeship::factory()->create();
    $first = Locality::factory()->for($voivodeship)->create(['name' => 'Kraków']);
    $second = Locality::factory()->for($voivodeship)->create(['name' => 'Kraków']);

    expect($first->slug)->toBe('krakow')
        ->and($second->slug)->toBe('krakow-2');
});

test('locality description is optional', function () {
    $locality = Locality::factory()->create(['description' => null]);

    expect($locality->description)->toBeNull();
});
