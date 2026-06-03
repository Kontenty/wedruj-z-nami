<?php

use App\Http\Resources\ObjectTypeResource;
use App\Models\ObjectType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('exposes recursive children', function () {
    $parent = ObjectType::factory()->create(['name' => 'Parent']);
    $child = ObjectType::factory()->for($parent, 'parent')->create(['name' => 'Child']);
    ObjectType::factory()->for($child, 'parent')->create(['name' => 'Grandchild']);

    $parent->load('childrenRecursive');

    $data = (new ObjectTypeResource($parent))->resolve(Request::create('/'));

    expect($data['children'])->toHaveCount(1)
        ->and($data['children'][0]['children'])->toHaveCount(1);
});
