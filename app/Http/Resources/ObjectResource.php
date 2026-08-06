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
            'locality' => $this->whenLoaded('locality', fn (): ?array => $this->locality ? [
                'name' => $this->locality->name,
                'slug' => $this->locality->slug,
                'voivodeship' => $this->locality->voivodeship ? [
                    'name' => $this->locality->voivodeship->name,
                    'slug' => $this->locality->voivodeship->slug,
                ] : null,
            ] : null),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_unesco' => $this->is_unesco,
            'url' => route('catalog.show', $this->slug),
            'thumbnail_url' => $this->thumbnail_url ?: '/images/placeholder-object-thumb.jpg',
            'thumbnail_webp_url' => $this->thumbnail_webp_url ?: null,
            'card_url' => $this->card_url ?: '/images/placeholder-object-card.jpg',
            'card_webp_url' => $this->card_webp_url ?: null,
            'primary_image_url' => $this->primary_image_url ?: '/images/placeholder-object.jpg',
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
