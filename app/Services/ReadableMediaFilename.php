<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ReadableMediaFilename
{
    public static function make(UploadedFile $file): string
    {
        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $baseName = $baseName !== '' ? $baseName : 'image';
        $extension = Str::lower($file->getClientOriginalExtension());

        return "{$baseName}-".Str::lower(Str::random(8)).".{$extension}";
    }
}
