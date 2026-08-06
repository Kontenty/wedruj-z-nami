<?php

use App\Http\Resources\ObjectResource;
use App\Models\Locality;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\Voivodeship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('exposes catalog object fields with detail url', function () {
    $locality = Locality::factory()->for(Voivodeship::factory()->create(['name' => 'Małopolskie', 'slug' => 'malopolskie']))->create(['name' => 'Kraków', 'slug' => 'krakow']);
    $type = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->for($locality)->create();
    $object->geojson = '{"type":"Point","coordinates":[19,50]}';
    $object->objectTypes()->attach($type);
    $object->load(['locality.voivodeship', 'objectTypes']);

    $data = (new ObjectResource($object))->toArray(Request::create('/'));

    expect($data)->toHaveKeys([
        'id', 'title', 'slug', 'description', 'latitude', 'longitude', 'is_unesco',
        'url', 'thumbnail_url', 'thumbnail_webp_url', 'card_url', 'card_webp_url', 'primary_image_url', 'locality', 'objectTypes', 'geojson',
    ])->and($data['url'])->toBe(route('catalog.show', $object->slug))
        ->and($data['locality']['name'])->toBe('Kraków')
        ->and($data['locality']['voivodeship']['name'])->toBe('Małopolskie')
        ->and($data['objectTypes'])->toHaveCount(1)
        ->and($data['thumbnail_url'])->toBe('/images/placeholder-object-thumb.jpg');
});
