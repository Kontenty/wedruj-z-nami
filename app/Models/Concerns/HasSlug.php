<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(function (Model $model): void {
            $sourceColumn = $model->getSlugSourceColumn();

            if ($model->slug !== null && $model->slug !== '' && ! $model->isDirty($sourceColumn)) {
                return;
            }

            $model->slug = $model->makeUniqueSlug((string) $model->{$sourceColumn});
        });
    }

    protected function getSlugSourceColumn(): string
    {
        return 'name';
    }

    protected function makeUniqueSlug(string $value): string
    {
        $baseSlug = Str::slug($value, '-', 'pl');
        $baseSlug = $baseSlug !== '' ? $baseSlug : Str::lower(class_basename($this));
        $slug = $baseSlug;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($this->exists, fn ($query) => $query->whereKeyNot($this->getKey()))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
