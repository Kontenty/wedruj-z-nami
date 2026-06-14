<?php

use App\Http\Resources\ObjectTypeResource;
use App\Models\ObjectType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('exposes flat type attributes', function () {
    $type = ObjectType::factory()->create(['name' => 'Zamki i pałace']);

    $data = (new ObjectTypeResource($type))->resolve(Request::create('/'));

    expect($data['id'])->toBe($type->id)
        ->and($data['name'])->toBe('Zamki i pałace')
        ->and($data['slug'])->toBe($type->slug)
        ->and($data)->not->toHaveKey('children');
});
