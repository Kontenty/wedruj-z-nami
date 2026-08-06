<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ObjectDetailResource extends JsonResource
{
    public function __construct($resource)
    {
        parent::__construct($resource);
        $this->withoutWrapping();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'lead' => $this->lead,
            'description' => trim(Str::markdown((string) $this->description)),
            'locality' => [
                'name' => $this->locality->name,
                'slug' => $this->locality->slug,
                'description' => $this->locality->description ? trim(Str::markdown((string) $this->locality->description)) : null,
                'voivodeship' => [
                    'name' => $this->locality->voivodeship->name,
                    'slug' => $this->locality->voivodeship->slug,
                ],
            ],
            'is_unesco' => $this->is_unesco,
            'opening_hours' => $this->opening_hours,
            'ticket_prices' => $this->ticket_prices,
            'accessibility' => $this->accessibility,
            'website' => $this->website,
            'data_source' => $this->data_source,
            'source_updated_at' => $this->source_updated_at?->locale('pl')->translatedFormat('j F Y'),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'objectTypes' => $this->objectTypes->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
            ]),
            'url' => route('catalog.show', $this->slug),
        ];
    }
}
