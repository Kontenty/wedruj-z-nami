<?php

use App\Services\ReadableMediaFilename;
use Illuminate\Http\UploadedFile;

test('it creates a readable unique filename from the original upload name', function () {
    $filename = ReadableMediaFilename::make(UploadedFile::fake()->image('Zamek Wawelski.jpg'));

    expect($filename)
        ->toMatch('/^zamek-wawelski-[a-z0-9]{8}\.jpg$/');
});

test('it uses image as the fallback when the original name has no slug', function () {
    $filename = ReadableMediaFilename::make(UploadedFile::fake()->create('!!!.jpg', 100, 'image/jpeg'));

    expect($filename)
        ->toMatch('/^image-[a-z0-9]{8}\.jpg$/');
});

test('it derives a safe extension from the detected mime type', function () {
    $filename = ReadableMediaFilename::make(UploadedFile::fake()->create('payload.php', 100, 'image/jpeg'));

    expect($filename)->toMatch('/^payload-[a-z0-9]{8}\.jpg$/');
});
