<?php

use App\Models\Article;
use App\Models\SightseeingObject;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection;

test('sightseeing object media accessors return fallback values without real images', function () {
    $object = SightseeingObject::factory()->create(['title' => 'Wawel Królewski']);

    expect($object->primary_image_url)->toBe('/images/placeholder-object.webp')
        ->and($object->thumbnail_url)->toBe('/images/placeholder-object-thumb.webp')
        ->and($object->card_url)->toBe('/images/placeholder-object-card.webp')
        ->and($object->has_images)->toBeFalse()
        ->and($object->image_urls)->toBe([])
        ->and($object->image_items)->toBe([]);
});

test('sightseeing object images store attribution metadata and expose resource payloads', function () {
    Storage::fake('public');

    $object = SightseeingObject::factory()->create(['title' => 'Wawel Królewski']);

    $media = $object
        ->addMedia(UploadedFile::fake()->image('wawel.jpg', 1000, 750))
        ->withCustomProperties([
            'author' => 'Jan Kowalski',
            'source' => 'Archiwum PTTK',
            'description' => 'Widok zamku od strony rzeki.',
            'alt' => 'Widok Wawelu',
        ])
        ->toMediaCollection('images');

    $object->refresh();
    $image = $object->image_items[0];

    expect($object->has_images)->toBeTrue()
        ->and($object->primary_image_url)->toEndWith('wawel-gallery_webp.webp')
        ->and($object->thumbnail_url)->toEndWith('wawel-thumbnail_webp.webp')
        ->and($object->card_url)->toEndWith('wawel-card_webp.webp')
        ->and($object->gallery_url)->toEndWith('wawel-gallery_webp.webp')
        ->and($object->image_urls)->toHaveCount(1)
        ->and($image)->toMatchArray([
            'id' => $media->id,
            'alt' => 'Widok Wawelu',
            'author' => 'Jan Kowalski',
            'source' => 'Archiwum PTTK',
            'description' => 'Widok zamku od strony rzeki.',
        ])
        ->and($image['url'])->toEndWith('wawel-gallery_webp.webp')
        ->and($image['thumbnail_url'])->toEndWith('wawel-thumbnail_webp.webp')
        ->and($image['card_url'])->toEndWith('wawel-card_webp.webp')
        ->and($image['gallery_url'])->toEndWith('wawel-gallery_webp.webp')
        ->and($image['order'])->toBeInt();

    foreach (['thumbnail_webp', 'card_webp', 'gallery_webp'] as $conversion) {
        expect($media->fresh()->hasGeneratedConversion($conversion))->toBeTrue()
            ->and(Storage::disk('public')->exists($media->getPathRelativeToRoot($conversion)))->toBeTrue();
    }

    expect(Storage::disk('public')->allFiles("{$media->id}/conversions"))
        ->toHaveCount(3)
        ->each->toEndWith('.webp');
});

test('sightseeing object media generates conversions immediately', function () {
    Storage::fake('public');

    $object = SightseeingObject::factory()->create();
    $media = $object
        ->addMedia(UploadedFile::fake()->image('immediate.jpg'))
        ->toMediaCollection('images');

    $object->refresh();

    expect($media->fresh()->generated_conversions)->toMatchArray([
        'thumbnail_webp' => true,
        'card_webp' => true,
        'gallery_webp' => true,
    ])
        ->and($object->primary_image_url)->toEndWith('immediate-gallery_webp.webp')
        ->and($object->thumbnail_url)->toEndWith('immediate-thumbnail_webp.webp')
        ->and($object->card_url)->toEndWith('immediate-card_webp.webp')
        ->and($object->gallery_url)->toEndWith('immediate-gallery_webp.webp');
});

test('article cover generates conversions immediately', function () {
    Storage::fake('public');

    $article = Article::factory()->create();
    $cover = $article
        ->addMedia(UploadedFile::fake()->image('immediate-cover.jpg'))
        ->toMediaCollection('cover');

    $article->refresh();

    expect($cover->fresh()->generated_conversions)->toMatchArray([
        'cover_webp' => true,
        'thumbnail_webp' => true,
    ])
        ->and($article->cover_image_url)->toEndWith('immediate-cover-cover_webp.webp')
        ->and($article->cover_thumbnail_url)->toEndWith('immediate-cover-thumbnail_webp.webp')
        ->and($article->cover_thumbnail_webp_url)->toEndWith('immediate-cover-thumbnail_webp.webp');
});

test('sightseeing object image payload falls back to object title when alt text is missing', function () {
    Storage::fake('public');

    $object = SightseeingObject::factory()->create(['title' => 'Zamek w Malborku']);

    $object
        ->addMedia(UploadedFile::fake()->image('malbork.jpg', 1200, 900))
        ->withCustomProperties([
            'author' => 'PTTK',
        ])
        ->toMediaCollection('images');

    $object->refresh();

    expect($object->image_items)->toHaveCount(1)
        ->and($object->image_items[0])->toHaveKeys([
            'id',
            'url',
            'thumbnail_url',
            'card_url',
            'gallery_url',
            'thumbnail_webp_url',
            'card_webp_url',
            'gallery_webp_url',
            'alt',
            'author',
            'source',
            'description',
            'order',
        ])
        ->and($object->image_items[0]['alt'])->toBe('Zamek w Malborku')
        ->and($object->image_items[0]['author'])->toBe('PTTK')
        ->and($object->image_items[0]['source'])->toBeNull()
        ->and($object->image_items[0]['description'])->toBeNull();
});

test('article media accessors return fallback cover values without real cover image', function () {
    $article = Article::factory()->create(['title' => 'Nowe obiekty w katalogu']);

    expect($article->cover_image_url)->toBe('/images/placeholder-news.webp')
        ->and($article->cover_thumbnail_url)->toBe('/images/placeholder-news-thumb.webp')
        ->and($article->has_cover_image)->toBeFalse()
        ->and($article->cover_image)->toMatchArray([
            'id' => null,
            'url' => '/images/placeholder-news.webp',
            'thumbnail_url' => '/images/placeholder-news-thumb.webp',
            'alt' => 'Nowe obiekty w katalogu',
            'author' => null,
            'source' => null,
        ]);
});

test('article cover is single file and exposes attribution metadata', function () {
    Storage::fake('public');

    $article = Article::factory()->create(['title' => 'Nowe obiekty w katalogu']);

    $article
        ->addMedia(UploadedFile::fake()->image('first-cover.jpg', 900, 600))
        ->toMediaCollection('cover');

    $cover = $article
        ->addMedia(UploadedFile::fake()->image('second-cover.jpg', 900, 600))
        ->withCustomProperties([
            'author' => 'Anna Nowak',
            'source' => 'PTTK',
            'alt' => 'Okładka aktualności',
        ])
        ->toMediaCollection('cover');

    $article->refresh();

    expect($article->getMedia('cover'))->toHaveCount(1)
        ->and($article->getFirstMedia('cover')->is($cover))->toBeTrue()
        ->and($article->has_cover_image)->toBeTrue()
        ->and($article->cover_image)->toMatchArray([
            'id' => $cover->id,
            'alt' => 'Okładka aktualności',
            'author' => 'Anna Nowak',
            'source' => 'PTTK',
        ])
        ->and($article->cover_image['url'])->toEndWith('second-cover-cover_webp.webp')
        ->and($article->cover_image['thumbnail_url'])->toEndWith('second-cover-thumbnail_webp.webp')
        ->and($cover->fresh()->hasGeneratedConversion('cover_webp'))->toBeTrue()
        ->and($cover->fresh()->hasGeneratedConversion('thumbnail_webp'))->toBeTrue()
        ->and(Storage::disk('public')->exists($cover->getPathRelativeToRoot('cover_webp')))->toBeTrue()
        ->and(Storage::disk('public')->exists($cover->getPathRelativeToRoot('thumbnail_webp')))->toBeTrue()
        ->and(Storage::disk('public')->allFiles("{$cover->id}/conversions"))->toHaveCount(2)
        ->each->toEndWith('.webp');
});

test('article cover payload falls back to article title when alt text is missing', function () {
    Storage::fake('public');

    $article = Article::factory()->create(['title' => 'Nowa trasa zwiedzania']);

    $article
        ->addMedia(UploadedFile::fake()->image('cover.jpg', 900, 600))
        ->withCustomProperties([
            'source' => 'PTTK archiwum',
        ])
        ->toMediaCollection('cover');

    $article->refresh();

    expect($article->cover_image)->toHaveKeys([
        'id',
        'url',
        'thumbnail_url',
        'alt',
        'author',
        'source',
    ])
        ->and($article->cover_image['alt'])->toBe('Nowa trasa zwiedzania')
        ->and($article->cover_image['author'])->toBeNull()
        ->and($article->cover_image['source'])->toBe('PTTK archiwum');
});

test('media collections reject unsupported mime types', function () {
    Storage::fake('public');

    $object = SightseeingObject::factory()->create();
    $article = Article::factory()->create();

    expect(fn () => $object
        ->addMedia(UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'))
        ->toMediaCollection('images'))
        ->toThrow(FileUnacceptableForCollection::class)
        ->and(fn () => $article
            ->addMedia(UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'))
            ->toMediaCollection('cover'))
        ->toThrow(FileUnacceptableForCollection::class);
});

test('media collections reject oversized files', function () {
    Storage::fake('public');

    $object = SightseeingObject::factory()->create();
    $article = Article::factory()->create();

    expect(fn () => $object
        ->addMedia(UploadedFile::fake()->create('too-large-object.jpg', 10 * 1024 + 1, 'image/jpeg'))
        ->toMediaCollection('images'))
        ->toThrow(FileUnacceptableForCollection::class)
        ->and(fn () => $article
            ->addMedia(UploadedFile::fake()->create('too-large-cover.jpg', 5 * 1024 + 1, 'image/jpeg'))
            ->toMediaCollection('cover'))
        ->toThrow(FileUnacceptableForCollection::class);
});

test('sightseeing object images can be strictly reordered', function () {
    Storage::fake('public');

    $object = SightseeingObject::factory()->create();
    $first = $object->addMedia(UploadedFile::fake()->image('first.jpg'))->toMediaCollection('images');
    $second = $object->addMedia(UploadedFile::fake()->image('second.jpg'))->toMediaCollection('images');

    $object->reorderImages([$second->id, $first->id]);

    $object->refresh();

    expect($object->getMedia('images')->pluck('id')->all())->toBe([$second->id, $first->id]);
    expect($object->primary_image_url)->toEndWith('second-gallery_webp.webp');
});

test('sightseeing object image reordering rejects invalid id lists', function () {
    Storage::fake('public');

    $object = SightseeingObject::factory()->create();
    $first = $object->addMedia(UploadedFile::fake()->image('first.jpg'))->toMediaCollection('images');
    $second = $object->addMedia(UploadedFile::fake()->image('second.jpg'))->toMediaCollection('images');
    $foreignObjectImage = SightseeingObject::factory()
        ->create()
        ->addMedia(UploadedFile::fake()->image('foreign.jpg'))
        ->toMediaCollection('images');
    $articleCover = Article::factory()
        ->create()
        ->addMedia(UploadedFile::fake()->image('cover.jpg'))
        ->toMediaCollection('cover');

    expect(fn () => $object->reorderImages([$first->id]))
        ->toThrow(InvalidArgumentException::class)
        ->and(fn () => $object->reorderImages([$first->id, $first->id]))
        ->toThrow(InvalidArgumentException::class)
        ->and(fn () => $object->reorderImages([$first->id, $foreignObjectImage->id]))
        ->toThrow(InvalidArgumentException::class)
        ->and(fn () => $object->reorderImages([$second->id, $articleCover->id]))
        ->toThrow(InvalidArgumentException::class);
});
