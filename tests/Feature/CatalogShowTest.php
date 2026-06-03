<?php

use App\Models\ObjectType;
use App\Models\SightseeingObject;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    fake()->unique(true);
});

it('renders the object detail page for a published object', function () {
    $object = SightseeingObject::factory()->published()->create();

    $this->get("/katalog/{$object->slug}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Show')
            ->has('object')
            ->has('images')
            ->has('geojson'));
});

it('returns 404 for a nonexistent slug', function () {
    $this->get('/katalog/nonexistent-slug')
        ->assertNotFound();
});

it('returns 404 for an unpublished object', function () {
    $object = SightseeingObject::factory()->create(['title' => 'Draft Object']);

    $this->get("/katalog/{$object->slug}")
        ->assertNotFound();
});

it('returns object with required fields', function () {
    $object = SightseeingObject::factory()->published()->create([
        'title' => 'Zamek Królewski',
        'lead' => 'Piękny zamek w centrum',
        'locality' => 'Warszawa',
        'is_unesco' => true,
        'opening_hours' => 'Pon-Pt 9:00-17:00',
        'ticket_prices' => '20 PLN',
        'website' => 'https://example.com',
    ]);

    $this->get("/katalog/{$object->slug}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('object.title', 'Zamek Królewski')
            ->where('object.lead', 'Piękny zamek w centrum')
            ->where('object.locality', 'Warszawa')
            ->where('object.is_unesco', true)
            ->where('object.opening_hours', 'Pon-Pt 9:00-17:00')
            ->where('object.ticket_prices', '20 PLN')
            ->where('object.website', 'https://example.com'));
});

it('renders markdown description as HTML', function () {
    $object = SightseeingObject::factory()->published()->create([
        'description' => 'Tekst **pogrubiony** i [link](https://example.com).',
    ]);

    $this->get("/katalog/{$object->slug}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('object.description', '<p>Tekst <strong>pogrubiony</strong> i <a href="https://example.com">link</a>.</p>'));
});

it('includes voivodeship data', function () {
    $object = SightseeingObject::factory()->published()->create();

    $this->get("/katalog/{$object->slug}")
        ->assertInertia(fn (Assert $page) => $page
            ->has('object.voivodeship.name')
            ->has('object.voivodeship.slug'));
});

it('includes object types', function () {
    $object = SightseeingObject::factory()->published()->create();
    $objectType = ObjectType::factory()->create();
    $object->objectTypes()->attach($objectType);

    $this->get("/katalog/{$object->slug}")
        ->assertInertia(fn (Assert $page) => $page
            ->has('object.objectTypes', 1));
});

it('returns images array from media library', function () {
    $object = SightseeingObject::factory()->published()->create();

    $testImage = storage_path('app/testing');
    if (! is_dir($testImage)) {
        mkdir($testImage, 0755, true);
    }
    $imagePath = $testImage.'/test-object.jpg';
    if (! file_exists($imagePath)) {
        $image = imagecreatetruecolor(100, 100);
        imagejpeg($image, $imagePath);
        imagedestroy($image);
    }

    $object->addMedia($imagePath)
        ->usingName('Test Image')
        ->withCustomProperties(['alt' => 'Test alt', 'author' => 'Author', 'source' => 'Source'])
        ->toMediaCollection('images');

    $this->get("/katalog/{$object->slug}")
        ->assertInertia(fn (Assert $page) => $page
            ->has('images', 1)
            ->has('images.0.url')
            ->has('images.0.thumbnail_url')
            ->has('images.0.card_url')
            ->has('images.0.alt')
            ->has('images.0.author')
            ->has('images.0.source')
            ->has('images.0.order'));
});

it('returns geojson for object with geometry', function () {
    $object = SightseeingObject::factory()->published()->point(52.2297, 21.0122)->create();

    $this->get("/katalog/{$object->slug}")
        ->assertInertia(fn (Assert $page) => $page
            ->has('geojson'));
});

it('returns valid geojson for object with polygon geometry', function () {
    $object = SightseeingObject::factory()->published()->polygon()->create();

    $this->get("/katalog/{$object->slug}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Show')
            ->has('geojson'));
});

it('returns url field with catalog show route', function () {
    $object = SightseeingObject::factory()->published()->create();

    $this->get("/katalog/{$object->slug}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('object.url', route('catalog.show', $object->slug)));
});
