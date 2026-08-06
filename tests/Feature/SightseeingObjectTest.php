<?php

use App\Models\Locality;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\Voivodeship;

test('sightseeing object factory creates required relationships', function () {
    $type = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->create();

    $object->objectTypes()->attach($type);

    expect($object->locality)->toBeInstanceOf(Locality::class)
        ->and($object->locality->voivodeship)->toBeInstanceOf(Voivodeship::class)
        ->and($object->objectTypes()->first()->is($type))->toBeTrue();
});

test('published scope only returns published objects', function () {
    $published = SightseeingObject::factory()->published()->create();
    SightseeingObject::factory()->create();

    expect(SightseeingObject::published()->orderBy('id')->pluck('id')->all())->toBe([$published->id]);
});

test('catalog filter scopes constrain objects by voivodeship unesco title and type', function () {
    $locality = Locality::factory()->for(Voivodeship::factory()->create(['name' => 'małopolskie']))->create(['name' => 'Kraków', 'slug' => 'krakow']);
    $otherLocality = Locality::factory()->for(Voivodeship::factory()->create(['name' => 'pomorskie']))->create(['name' => 'Gdańsk', 'slug' => 'gdansk']);
    $type = ObjectType::factory()->create(['name' => 'Zamki i pałace']);
    $matching = SightseeingObject::factory()
        ->for($locality)
        ->published()
        ->unesco()
        ->create(['title' => 'Wawel Królewski']);
    $nonMatching = SightseeingObject::factory()
        ->for($otherLocality)
        ->published()
        ->create(['title' => 'Westerplatte']);

    $matching->objectTypes()->attach($type);
    $nonMatching->objectTypes()->attach($type);

    $results = SightseeingObject::query()
        ->inVoivodeship('malopolskie')
        ->unesco()
        ->searchByTitle('wawel')
        ->inCategory($type)
        ->pluck('id')
        ->all();

    expect($results)->toBe([$matching->id]);
});

test('nearby scope returns up to three nearest published objects within radius', function () {
    $nearest = SightseeingObject::factory()->published()->point(50.0610, 19.9370)->create(['title' => 'Najbliższy obiekt']);
    $second = SightseeingObject::factory()->published()->point(50.0700, 19.9500)->create(['title' => 'Drugi obiekt']);
    $third = SightseeingObject::factory()->published()->polygon()->create(['title' => 'Obiekt obszarowy']);
    SightseeingObject::factory()->published()->point(52.2297, 21.0122)->create(['title' => 'Poza promieniem']);
    SightseeingObject::factory()->point(50.0620, 19.9380)->create(['title' => 'Nieopublikowany blisko']);

    $results = SightseeingObject::nearby(50.0614, 19.9372)->get();

    expect($results)->toHaveCount(3)
        ->and($results->pluck('id')->all())->toBe([$nearest->id, $third->id, $second->id])
        ->and($results->first()->distance_meters)->toBeNumeric();
});

test('sightseeing object slugs are generated with collision handling', function () {
    $first = SightseeingObject::factory()->create(['title' => 'Wawel Królewski']);
    $second = SightseeingObject::factory()->create(['title' => 'Wawel Królewski']);

    expect($first->slug)->toBe('wawel-krolewski')
        ->and($second->slug)->toBe('wawel-krolewski-2');
});
