<?php

use App\Filament\Resources\ObjectCollections\Pages\CreateObjectCollection;
use App\Filament\Resources\ObjectCollections\Pages\EditObjectCollection;
use App\Models\ObjectCollection;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\User;
use Filament\Forms\Components\MarkdownEditor;
use Livewire\Livewire;

test('editor can create a collection with categories and ordered objects', function () {
    $editor = User::factory()->editor()->create();
    $type = ObjectType::factory()->create();
    $firstObject = SightseeingObject::factory()->create();
    $secondObject = SightseeingObject::factory()->create();

    $this->actingAs($editor);

    Livewire::test(CreateObjectCollection::class)
        ->fillForm([
            'name' => 'Zamki Małopolski',
            'description' => 'Wybrane zamki regionu.',
            'objectTypes' => [$type->id],
            'members' => [
                ['sightseeing_object_id' => $secondObject->id],
                ['sightseeing_object_id' => $firstObject->id],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $collection = ObjectCollection::query()->where('name', 'Zamki Małopolski')->firstOrFail();

    expect($collection->objectTypes->modelKeys())->toBe([$type->id])
        ->and($collection->sightseeingObjects->modelKeys())->toBe([$secondObject->id, $firstObject->id])
        ->and($collection->sightseeingObjects->first()->pivot->position)->toBe(1)
        ->and($collection->sightseeingObjects->last()->pivot->position)->toBe(2);
});

test('editor can update collection categories, membership, and member order', function () {
    $editor = User::factory()->editor()->create();
    $oldType = ObjectType::factory()->create();
    $newType = ObjectType::factory()->create();
    $firstObject = SightseeingObject::factory()->create();
    $secondObject = SightseeingObject::factory()->create();
    $thirdObject = SightseeingObject::factory()->create();
    $collection = ObjectCollection::factory()->create();
    $collection->objectTypes()->attach($oldType);
    $collection->sightseeingObjects()->attach($firstObject, ['position' => 1]);
    $collection->sightseeingObjects()->attach($secondObject, ['position' => 2]);

    $this->actingAs($editor);

    Livewire::test(EditObjectCollection::class, ['record' => $collection->getKey()])
        ->assertFormFieldExists('description', fn (MarkdownEditor $field): bool => $field instanceof MarkdownEditor)
        ->fillForm([
            'name' => $collection->name,
            'description' => '**Zaktualizowany** opis kolekcji.',
            'objectTypes' => [$newType->id],
            'members' => [
                ['sightseeing_object_id' => $thirdObject->id],
                ['sightseeing_object_id' => $firstObject->id],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $collection->refresh();

    expect($collection->description)->toBe('**Zaktualizowany** opis kolekcji.')
        ->and($collection->objectTypes->modelKeys())->toBe([$newType->id])
        ->and($collection->sightseeingObjects->modelKeys())->toBe([$thirdObject->id, $firstObject->id])
        ->and($collection->sightseeingObjects->first()->pivot->position)->toBe(1)
        ->and($collection->sightseeingObjects->last()->pivot->position)->toBe(2);
});

test('editor can open the public collection preview in a new tab', function () {
    $editor = User::factory()->editor()->create();
    $collection = ObjectCollection::factory()->create();

    $this->actingAs($editor);

    Livewire::test(EditObjectCollection::class, ['record' => $collection->getKey()])
        ->assertActionHasLabel('view', 'Zobacz obiekt')
        ->assertActionHasUrl('view', route('catalog.collection.show', $collection))
        ->assertActionShouldOpenUrlInNewTab('view');
});
