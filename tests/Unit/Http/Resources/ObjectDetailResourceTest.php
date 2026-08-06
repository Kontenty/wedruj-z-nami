<?php

use App\Http\Resources\ObjectDetailResource;
use App\Models\Locality;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\Voivodeship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('exposes object detail metadata fields', function () {
    $locality = Locality::factory()->for(Voivodeship::factory()->create(['name' => 'Małopolskie', 'slug' => 'malopolskie']))->create(['name' => 'Kraków', 'slug' => 'krakow']);
    $type = ObjectType::factory()->create(['name' => 'Zamek', 'slug' => 'zamek']);
    $object = SightseeingObject::factory()->for($locality)->create([
        'title' => 'Zamek Królewski',
        'data_source' => 'PTTK Warszawa',
        'source_updated_at' => '2026-06-03',
    ]);

    $object->objectTypes()->attach($type);
    $object->load(['locality.voivodeship', 'objectTypes']);

    $data = (new ObjectDetailResource($object))->toArray(Request::create('/'));

    expect($data)->toHaveKeys([
        'id',
        'title',
        'slug',
        'lead',
        'description',
        'locality',
        'is_unesco',
        'opening_hours',
        'ticket_prices',
        'accessibility',
        'website',
        'data_source',
        'source_updated_at',
        'latitude',
        'longitude',
        'objectTypes',
        'url',
    ])->and($data['data_source'])->toBe('PTTK Warszawa')
        ->and($data['source_updated_at'])->toBe('3 czerwca 2026')
        ->and($data['locality']['name'])->toBe('Kraków')
        ->and($data['locality']['voivodeship']['name'])->toBe('Małopolskie')
        ->and($data['objectTypes'])->toHaveCount(1)
        ->and($data['url'])->toBe(route('catalog.show', $object->slug));
});
