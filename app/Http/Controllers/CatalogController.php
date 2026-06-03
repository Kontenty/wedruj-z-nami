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
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $query = SightseeingObject::query()
            ->published()
            ->with(['voivodeship', 'objectTypes'])
            ->select('sightseeing_objects.*')
            ->searchByTitle($request->query('q'))
            ->inVoivodeship($request->query('wojewodztwo'))
            ->inObjectType($request->integer('objectType') ?: null)
            ->unesco($request->boolean('unesco'));

        $objects = (clone $query)
            ->orderByDesc('published_at')
            ->paginate(24)
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
                'wojewodztwo' => $request->query('wojewodztwo'),
                'objectType' => $request->query('objectType'),
                'unesco' => $request->boolean('unesco'),
            ],
            'objectTypes' => ObjectTypeResource::collection(
                ObjectType::query()
                    ->whereNull('parent_id')
                    ->with('childrenRecursive')
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
            ->with(['voivodeship', 'objectTypes', 'media'])
            ->select('sightseeing_objects.*')
            ->selectRaw('ST_AsGeoJSON(geometry) as geojson')
            ->firstOrFail();

        $nearby = SightseeingObject::query()
            ->with(['voivodeship', 'objectTypes', 'media'])
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
