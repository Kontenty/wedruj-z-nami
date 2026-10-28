<?php

namespace App\Http\Controllers;

use App\Http\Resources\CatalogEntryResource;
use App\Http\Resources\CollectionDetailResource;
use App\Http\Resources\ObjectDetailResource;
use App\Http\Resources\ObjectResource;
use App\Http\Resources\ObjectTypeResource;
use App\Models\ObjectCollection;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\Voivodeship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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

        $collectionQuery = ObjectCollection::query()
            ->select('id')
            ->selectRaw("'collection' as type")
            ->selectRaw('created_at as sort_date')
            ->when($voivodeships !== [], fn (Builder $query) => $query->whereHas(
                'sightseeingObjects.locality.voivodeship',
                fn (Builder $query) => $query->whereIn('slug', $voivodeships),
            ))
            ->when($objectTypes !== [], fn (Builder $query) => $query->whereHas(
                'objectTypes',
                fn (Builder $query) => $query->whereIn('object_types.id', $objectTypes),
            ))
            ->when($request->boolean('unesco'), fn (Builder $query) => $query->whereHas(
                'sightseeingObjects',
                fn (Builder $query) => $query->where('is_unesco', true),
            ));

        $catalogEntries = DB::query()
            ->fromSub(
                (clone $query)
                    ->select('sightseeing_objects.id')
                    ->selectRaw("'object' as type")
                    ->selectRaw('published_at as sort_date')
                    ->toBase()
                    ->unionAll($collectionQuery->toBase()),
                'catalog_entries',
            )
            ->orderByDesc('sort_date')
            ->orderBy('type')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $entries = $catalogEntries->getCollection();
        $objects = SightseeingObject::query()
            ->with(['locality.voivodeship', 'objectTypes'])
            ->whereIn('id', $entries->where('type', 'object')->pluck('id'))
            ->get()
            ->keyBy('id');
        $collections = ObjectCollection::query()
            ->with([
                'objectTypes',
                'sightseeingObjects' => fn (BelongsToMany $query) => $query
                    ->with('media')
                    ->limit(1),
            ])
            ->whereIn('id', $entries->where('type', 'collection')->pluck('id'))
            ->get()
            ->keyBy('id');

        $catalogEntries->setCollection($entries->map(fn (object $entry): SightseeingObject|ObjectCollection => $entry->type === 'object'
                ? $objects->get($entry->id)
                : $collections->get($entry->id)));

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
            'objects' => CatalogEntryResource::collection($catalogEntries),
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

    public function showCollection(ObjectCollection $collection): Response
    {
        $collection->load(['sightseeingObjects' => fn (BelongsToMany $query) => $query
            ->with(['locality.voivodeship', 'objectTypes', 'media'])]);

        return Inertia::render('Catalog/Collection', [
            'collection' => new CollectionDetailResource($collection),
        ]);
    }
}
