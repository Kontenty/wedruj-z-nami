<?php

use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\Voivodeship;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    fake()->unique(true);
});

it('returns the catalog inertia page', function () {
    SightseeingObject::factory()->published()->create();

    $this->get('/katalog')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Index')
            ->has('objects')
            ->has('mapObjects')
            ->has('filters')
            ->has('objectTypes')
            ->has('voivodeships'));
});

it('filters by voivodeship slug', function () {
    $mazowieckie = Voivodeship::factory()->create(['name' => 'Mazowieckie', 'slug' => 'mazowieckie']);
    $malopolskie = Voivodeship::factory()->create(['slug' => 'malopolskie']);
    SightseeingObject::factory()->published()->for($mazowieckie)->create(['title' => 'Warszawski zamek']);
    SightseeingObject::factory()->published()->for($malopolskie)->create(['title' => 'Krakowski zamek']);

    $this->get('/katalog?'.http_build_query(['voivodeships' => ['mazowieckie']]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.title', 'Warszawski zamek')
            ->missing('objects.data.1'));
});

it('filters by object type including descendants', function () {
    $parent = ObjectType::factory()->create();
    $child = ObjectType::factory()->for($parent, 'parent')->create();
    $included = SightseeingObject::factory()->published()->create(['title' => 'Included child type']);
    $excluded = SightseeingObject::factory()->published()->create(['title' => 'Excluded type']);
    $included->objectTypes()->attach($child);

    $this->get('/katalog?'.http_build_query(['objectTypes' => [$parent->id]]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.title', 'Included child type')
            ->missing('objects.data.1'));
});

it('filters by unesco', function () {
    SightseeingObject::factory()->published()->unesco()->create(['title' => 'UNESCO object']);
    SightseeingObject::factory()->published()->create(['title' => 'Regular object']);

    $this->get('/katalog?unesco=true')
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.title', 'UNESCO object')
            ->missing('objects.data.1'));
});

it('includes the unesco flag in catalog objects', function () {
    SightseeingObject::factory()->published()->unesco()->create([
        'published_at' => now(),
        'title' => 'UNESCO object',
    ]);

    SightseeingObject::factory()->published()->create([
        'published_at' => now()->subDay(),
        'title' => 'Regular object',
    ]);

    $this->get('/katalog')
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.is_unesco', true)
            ->where('objects.data.1.is_unesco', false));
});

it('searches by partial title phrase', function () {
    SightseeingObject::factory()->published()->create(['title' => 'Zamek w Malborku']);
    SightseeingObject::factory()->published()->create(['title' => 'Muzeum regionalne']);

    $this->get('/katalog?q=zamek')
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.title', 'Zamek w Malborku')
            ->missing('objects.data.1'));
});

it('combines filters correctly', function () {
    $voivodeship = Voivodeship::factory()->create(['name' => 'małopolskie', 'slug' => 'malopolskie']);
    SightseeingObject::factory()->published()->unesco()->for($voivodeship)->create(['title' => 'zamek UNESCO']);
    SightseeingObject::factory()->published()->for($voivodeship)->create(['title' => 'zamek zwykly']);

    $this->get('/katalog?'.http_build_query(['q' => 'zamek', 'voivodeships' => ['malopolskie'], 'unesco' => 'true']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.title', 'zamek UNESCO')
            ->missing('objects.data.1'));
});

it('excludes unpublished objects from list and map', function () {
    SightseeingObject::factory()->published()->create(['title' => 'Published']);
    SightseeingObject::factory()->create(['title' => 'Draft']);

    $this->get('/katalog')
        ->assertInertia(fn (Assert $page) => $page
            ->has('objects.data', 1)
            ->has('mapObjects.data', 1));
});

it('paginates objects at 24 per page and keeps map unpaginated', function () {
    $voivodeship = Voivodeship::factory()->create();

    foreach (range(1, 25) as $index) {
        SightseeingObject::factory()->published()->for($voivodeship)->create(['title' => "Paginated object {$index}"]);
    }

    $this->get('/katalog')
        ->assertInertia(fn (Assert $page) => $page
            ->has('objects.data', 24)
            ->has('mapObjects.data', 25));
});
