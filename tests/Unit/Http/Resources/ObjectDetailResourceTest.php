<?php

use App\Http\Resources\ObjectDetailResource;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\Voivodeship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('exposes object detail metadata fields', function () {
    $voivodeship = Voivodeship::factory()->create(['name' => 'Małopolskie', 'slug' => 'malopolskie']);
    $type = ObjectType::factory()->create(['name' => 'Zamek', 'slug' => 'zamek']);
    $object = SightseeingObject::factory()->for($voivodeship)->create([
        'title' => 'Zamek Królewski',
        'data_source' => 'PTTK Warszawa',
        'source_updated_at' => '2026-06-03',
    ]);

    $object->objectTypes()->attach($type);
    $object->load(['voivodeship', 'objectTypes']);

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
        'voivodeship',
        'objectTypes',
        'url',
    ])->and($data['data_source'])->toBe('PTTK Warszawa')
        ->and($data['source_updated_at'])->toBe('3 czerwca 2026')
        ->and($data['voivodeship']['name'])->toBe('Małopolskie')
        ->and($data['objectTypes'])->toHaveCount(1)
        ->and($data['url'])->toBe(route('catalog.show', $object->slug));
});
