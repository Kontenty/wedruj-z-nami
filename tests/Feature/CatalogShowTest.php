<?php

use App\Models\Locality;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\Voivodeship;
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
            ->has('geojson')
            ->has('nearby', 0));
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
    $locality = Locality::factory()->for(Voivodeship::factory()->create())->create(['name' => 'Warszawa', 'slug' => 'warszawa']);
    $object = SightseeingObject::factory()->published()->for($locality)->create([
        'title' => 'Zamek Królewski',
        'lead' => 'Piękny zamek w centrum',
        'is_unesco' => true,
        'opening_hours' => 'Pon-Pt 9:00-17:00',
        'ticket_prices' => '20 PLN',
        'accessibility' => 'Podjazd dla wózków i toaleta dostępna.',
        'website' => 'https://example.com',
        'data_source' => 'PTTK Warszawa',
        'source_updated_at' => '2026-06-03',
    ]);

    $this->get("/katalog/{$object->slug}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('object.title', 'Zamek Królewski')
            ->where('object.lead', 'Piękny zamek w centrum')
            ->where('object.locality.name', 'Warszawa')
            ->where('object.is_unesco', true)
            ->where('object.opening_hours', 'Pon-Pt 9:00-17:00')
            ->where('object.ticket_prices', '20 PLN')
            ->where('object.accessibility', 'Podjazd dla wózków i toaleta dostępna.')
            ->where('object.website', 'https://example.com')
            ->where('object.data_source', 'PTTK Warszawa')
            ->where('object.source_updated_at', '3 czerwca 2026'));
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
            ->has('object.locality.voivodeship.name')
            ->has('object.locality.voivodeship.slug'));
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
            ->has('images.0.gallery_url')
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

it('includes nearby objects ordered by distance and limited to three', function () {
    $origin = SightseeingObject::factory()->published()->point(52.2297, 21.0122)->create();

    SightseeingObject::factory()->published()->point(52.2310, 21.0130)->create(['title' => 'Najbliższy']);
    SightseeingObject::factory()->published()->point(52.2350, 21.0180)->create(['title' => 'Drugi']);
    SightseeingObject::factory()->published()->point(52.2450, 21.0250)->create(['title' => 'Trzeci']);
    SightseeingObject::factory()->published()->point(52.2550, 21.0400)->create(['title' => 'Czwarty']);
    SightseeingObject::factory()->published()->point(52.5200, 21.4500)->create(['title' => 'Za daleko']);
    SightseeingObject::factory()->point(52.2320, 21.0140)->create(['title' => 'Szkic']);

    $this->get("/katalog/{$origin->slug}")
        ->assertInertia(fn (Assert $page) => $page
            ->has('nearby', 3)
            ->where('nearby.0.title', 'Najbliższy')
            ->where('nearby.1.title', 'Drugi')
            ->where('nearby.2.title', 'Trzeci'));
});

it('excludes the current object from nearby results', function () {
    $origin = SightseeingObject::factory()->published()->point(52.2297, 21.0122)->create(['title' => 'Obiekt źródłowy']);
    SightseeingObject::factory()->published()->point(52.2310, 21.0130)->create(['title' => 'Sąsiad']);

    $this->get("/katalog/{$origin->slug}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('nearby.0.title', 'Sąsiad')
            ->missing('nearby.1'));
});

it('returns an empty nearby collection when no nearby objects exist', function () {
    $origin = SightseeingObject::factory()->published()->point(52.2297, 21.0122)->create();
    SightseeingObject::factory()->published()->point(53.4285, 14.5528)->create(['title' => 'Szczecin']);

    $this->get("/katalog/{$origin->slug}")
        ->assertInertia(fn (Assert $page) => $page
            ->has('nearby', 0));
});

it('uses polygon centroid for polygon origin objects', function () {
    $origin = SightseeingObject::factory()->published()->polygon()->create([
        'title' => 'Obszar testowy',
        'latitude' => 54.3520,
        'longitude' => 18.6466,
    ]);

    SightseeingObject::factory()->published()->point(50.0648, 19.9451)->create(['title' => 'Blisko centroidu']);
    SightseeingObject::factory()->published()->point(54.3522, 18.6468)->create(['title' => 'Blisko współrzędnych']);

    $this->get("/katalog/{$origin->slug}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('nearby.0.title', 'Blisko centroidu')
            ->missing('nearby.1'));
});

it('uses polygon centroid for polygon candidate objects', function () {
    $origin = SightseeingObject::factory()->published()->point(50.0647, 19.9450)->create();

    SightseeingObject::factory()->published()->polygon()->create([
        'title' => 'Polygon sąsiad',
        'latitude' => 54.3520,
        'longitude' => 18.6466,
    ]);
    SightseeingObject::factory()->published()->point(50.1400, 20.0300)->create(['title' => 'Dalszy punkt']);

    $this->get("/katalog/{$origin->slug}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('nearby.0.title', 'Polygon sąsiad'));
});
