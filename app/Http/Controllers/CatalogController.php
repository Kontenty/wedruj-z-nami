<?php

namespace App\Http\Controllers;

use App\Http\Resources\ObjectDetailResource;
use App\Http\Resources\ObjectResource;
use App\Http\Resources\ObjectTypeResource;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\Voivodeship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $voivodeships = array_values(array_filter(Arr::wrap($request->query('voivodeships', []))));
        $objectTypes = array_values(array_filter(array_map('intval', Arr::wrap($request->query('objectTypes', [])))));

        $query = SightseeingObject::query()
            ->published()
            ->with(['locality.voivodeship', 'objectTypes'])
            ->select('sightseeing_objects.*')
            ->searchByTitle($request->query('q'))
            ->when($voivodeships !== [], fn (Builder $query) => $query->whereHas('locality.voivodeship', fn (Builder $query) => $query->whereIn('slug', $voivodeships)))
            ->when($objectTypes !== [], function (Builder $query) use ($objectTypes): void {
                $query->whereHas('objectTypes', fn (Builder $query) => $query->whereIn('object_types.id', $objectTypes));
            })
            ->unesco($request->boolean('unesco'));

        $objects = (clone $query)
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        $mapObjects = (clone $query)
            ->addSelect('sightseeing_objects.*')
            ->selectRaw('ST_AsGeoJSON(geometry) as geojson')
            ->where(function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->whereNotNull('latitude')
                        ->whereNotNull('longitude');
                })->orWhereNotNull('geometry');
            })
            ->orderByDesc('published_at')
            ->get();

        return Inertia::render('Catalog/Index', [
            'objects' => ObjectResource::collection($objects),
            'mapObjects' => ObjectResource::collection($mapObjects),
            'filters' => [
                'q' => $request->query('q'),
                'voivodeships' => $voivodeships,
                'objectTypes' => array_map('strval', $objectTypes),
                'unesco' => $request->boolean('unesco'),
            ],
            'objectTypes' => ObjectTypeResource::collection(
                ObjectType::query()
                    ->orderBy('name')
                    ->get()
            ),
            'voivodeships' => Voivodeship::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
        ]);
    }

    public function show(SightseeingObject $object): Response
    {
        $object = SightseeingObject::query()
            ->published()
            ->whereKey($object)
            ->with(['locality.voivodeship', 'objectTypes', 'media'])
            ->select('sightseeing_objects.*')
            ->selectRaw('ST_AsGeoJSON(geometry) as geojson')
            ->firstOrFail();

        $nearby = SightseeingObject::query()
            ->with(['locality.voivodeship', 'objectTypes', 'media'])
            ->nearby($object)
            ->get();

        return Inertia::render('Catalog/Show', [
            'object' => new ObjectDetailResource($object),
            'images' => $object->image_items,
            'geojson' => $object->geojson,
            'nearby' => ObjectResource::collection($nearby),
        ]);
    }
}
