<?php

use App\Models\ObjectCollection;
use App\Models\SightseeingObject;

it('keeps object membership positions independent across collections', function () {
    $first = SightseeingObject::factory()->create(['title' => 'First object']);
    $second = SightseeingObject::factory()->create(['title' => 'Second object']);
    $third = SightseeingObject::factory()->create(['title' => 'Third object']);

    $firstCollection = ObjectCollection::factory()->create();
    $secondCollection = ObjectCollection::factory()->create();

    $firstCollection->sightseeingObjects()->attach($first, ['position' => 1]);
    $firstCollection->sightseeingObjects()->attach($second, ['position' => 2]);
    $secondCollection->sightseeingObjects()->attach($first, ['position' => 2]);
    $secondCollection->sightseeingObjects()->attach($third, ['position' => 1]);

    expect($firstCollection->fresh()->sightseeingObjects->modelKeys())->toBe([$first->id, $second->id])
        ->and($secondCollection->fresh()->sightseeingObjects->modelKeys())->toBe([$third->id, $first->id]);
});
