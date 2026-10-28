<?php

namespace App\Http\Resources;

use App\Models\ObjectCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class CollectionDetailResource extends JsonResource
{
    public function __construct(ObjectCollection $resource)
    {
        parent::__construct($resource);
        $this->withoutWrapping();
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ObjectCollection $collection */
        $collection = $this->resource;
        $firstObject = $collection->sightseeingObjects->first();

        return [
            'id' => $collection->id,
            'name' => $collection->name,
            'slug' => $collection->slug,
            'description' => trim(Str::markdown((string) $collection->description)),
            'primary_image_url' => $firstObject?->primary_image_url ?: '/images/placeholder-object.webp',
            'sightseeingObjects' => ObjectResource::collection($collection->sightseeingObjects)
                ->resolve($request),
        ];
    }
}
