<?php

namespace App\Http\Resources;

use App\Models\ObjectCollection;
use App\Models\SightseeingObject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class CatalogEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof SightseeingObject) {
            return [
                ...ObjectResource::make($this->resource)->resolve($request),
                'type' => 'object',
            ];
        }

        /** @var ObjectCollection $collection */
        $collection = $this->resource;
        $firstObject = $collection->sightseeingObjects->first();

        return [
            'id' => $collection->id,
            'type' => 'collection',
            'title' => $collection->name,
            'slug' => $collection->slug,
            'url' => route('catalog.collection.show', $collection->slug),
            'description' => Str::limit($collection->description, 150),
            'locality' => null,
            'latitude' => null,
            'longitude' => null,
            'is_unesco' => false,
            'thumbnail_url' => $firstObject?->thumbnail_url ?: '/images/placeholder-object-thumb.webp',
            'thumbnail_webp_url' => $firstObject?->thumbnail_webp_url ?: null,
            'card_url' => $firstObject?->card_url ?: '/images/placeholder-object-card.webp',
            'card_webp_url' => $firstObject?->card_webp_url ?: null,
            'primary_image_url' => $firstObject?->primary_image_url ?: '/images/placeholder-object.webp',
            'objectTypes' => $collection->objectTypes
                ->map(fn ($objectType): array => [
                    'id' => $objectType->id,
                    'name' => $objectType->name,
                    'slug' => $objectType->slug,
                ])
                ->values(),
        ];
    }
}
