<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ObjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => Str::limit((string) $this->description, 150),
            'locality' => $this->locality,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_unesco' => $this->is_unesco,
            'url' => route('catalog.show', $this->slug),
            'thumbnail_url' => $this->thumbnail_url ?: '/images/placeholder-object-thumb.jpg',
            'thumbnail_webp_url' => $this->thumbnail_webp_url ?: null,
            'card_url' => $this->card_url ?: '/images/placeholder-object-card.jpg',
            'card_webp_url' => $this->card_webp_url ?: null,
            'primary_image_url' => $this->primary_image_url ?: '/images/placeholder-object.jpg',
            'voivodeship' => $this->whenLoaded('voivodeship', fn (): ?array => $this->voivodeship ? [
                'name' => $this->voivodeship->name,
                'slug' => $this->voivodeship->slug,
            ] : null),
            'objectTypes' => $this->whenLoaded('objectTypes', fn () => $this->objectTypes
                ->map(fn ($objectType): array => [
                    'id' => $objectType->id,
                    'name' => $objectType->name,
                    'slug' => $objectType->slug,
                ])
                ->values()),
            'geojson' => $this->geojson,
        ];
    }
}
