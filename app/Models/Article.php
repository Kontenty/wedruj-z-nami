<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'slug', 'excerpt', 'body', 'status', 'published', 'published_at'])]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, HasSlug;

    protected $attributes = [
        'published' => false,
        'status' => 'draft',
    ];

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('published', true)->orderByDesc('published_at');
    }

    protected function getSlugSourceColumn(): string
    {
        return 'title';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
