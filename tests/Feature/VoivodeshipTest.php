<?php

use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\Voivodeship;
use Database\Seeders\DatabaseSeeder;

test('database seeder creates all polish voivodeships', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Voivodeship::query()->count())->toBe(16)
        ->and(Voivodeship::query()->where('slug', 'malopolskie')->exists())->toBeTrue()
        ->and(Voivodeship::query()->where('slug', 'warminsko-mazurskie')->exists())->toBeTrue();
});

test('database seeder attaches object types to seeded sightseeing objects', function () {
    $this->seed(DatabaseSeeder::class);

    $wawel = SightseeingObject::query()->where('title', 'Wawel - Zamek Królewski')->firstOrFail();
    $castleType = ObjectType::query()->where('name', 'Zamki')->firstOrFail();

    expect($wawel->objectTypes()->whereKey($castleType->id)->exists())->toBeTrue();
});

test('database seeder creates the biebrzanski national park', function () {
    $this->seed(DatabaseSeeder::class);

    $biebrza = SightseeingObject::query()->where('title', 'Biebrzański Park Narodowy')->firstOrFail();
    $nationalParks = ObjectType::query()->where('name', 'Parki narodowe')->firstOrFail();

    expect($biebrza->voivodeship->slug)->toBe('podlaskie')
        ->and($biebrza->objectTypes()->whereKey($nationalParks->id)->exists())->toBeTrue();
});

test('voivodeship has sightseeing objects', function () {
    $voivodeship = Voivodeship::factory()->create();
    $object = SightseeingObject::factory()->for($voivodeship)->create();

    expect($voivodeship->sightseeingObjects()->first()->is($object))->toBeTrue();
});

test('voivodeship slugs are generated with collision handling', function () {
    $first = Voivodeship::query()->create(['name' => 'małopolskie']);
    $second = Voivodeship::query()->create(['name' => 'małopolskie']);

    expect($first->slug)->toBe('malopolskie')
        ->and($second->slug)->toBe('malopolskie-2');
});
