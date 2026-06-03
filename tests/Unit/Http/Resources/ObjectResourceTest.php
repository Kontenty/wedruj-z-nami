<?php

use App\Http\Resources\ObjectResource;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\Voivodeship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('exposes catalog object fields without detail url', function () {
    $voivodeship = Voivodeship::factory()->create(['name' => 'Małopolskie', 'slug' => 'malopolskie']);
    $type = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->for($voivodeship)->create();
    $object->geojson = '{"type":"Point","coordinates":[19,50]}';
    $object->objectTypes()->attach($type);
    $object->load(['voivodeship', 'objectTypes']);

    $data = (new ObjectResource($object))->toArray(Request::create('/'));

    expect($data)->toHaveKeys([
        'id', 'title', 'slug', 'description', 'latitude', 'longitude', 'is_unesco',
        'thumbnail_url', 'primary_image_url', 'voivodeship', 'objectTypes', 'geojson',
    ])->not->toHaveKey('url')
        ->and($data['voivodeship']['name'])->toBe('Małopolskie')
        ->and($data['objectTypes'])->toHaveCount(1)
        ->and($data['thumbnail_url'])->toBe('/images/placeholder-object-thumb.jpg');
});
