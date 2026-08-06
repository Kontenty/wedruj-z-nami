<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\Voivodeship;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $catalogStats = $this->catalogStats();
        $browseTypes = $this->browseTypes();
        $latestObjects = $this->latestObjects();
        $latestNews = $this->latestNews();

        return view('home', compact('browseTypes', 'catalogStats', 'latestObjects', 'latestNews'));
    }

    /**
     * @return array{objects: int, object_types: int, voivodeships: int}
     */
    private function catalogStats(): array
    {
        return Cache::remember(
            'home:catalog-stats',
            now()->addMinutes(15),
            fn (): array => [
                'objects' => SightseeingObject::published()->count(),
                'object_types' => ObjectType::query()
                    ->whereHas('sightseeingObjects', fn (Builder $query): Builder => $query->published())
                    ->count(),
                'voivodeships' => Voivodeship::query()
                    ->whereHas('localities.sightseeingObjects', fn (Builder $query): Builder => $query->published())
                    ->count(),
            ],
        );
    }

    private function browseTypes(): Collection
    {
        $browseTypeIds = Cache::remember(
            'home:browse-type-ids',
            now()->addMinutes(15),
            fn (): array => ObjectType::query()
                ->withCount([
                    'sightseeingObjects as published_objects_count' => fn (Builder $query): Builder => $query->published(),
                ])
                ->having('published_objects_count', '>', 0)
                ->orderByDesc('published_objects_count')
                ->orderBy('name')
                ->limit(6)
                ->pluck('id')
                ->all(),
        );

        return $this->sortByCachedIds(
            ObjectType::query()
                ->withCount([
                    'sightseeingObjects as published_objects_count' => fn (Builder $query): Builder => $query->published(),
                ])
                ->whereKey($browseTypeIds)
                ->get(),
            $browseTypeIds,
        );
    }

    private function latestObjects(): Collection
    {
        $latestObjectIds = Cache::remember(
            'home:latest-object-ids',
            now()->addMinutes(5),
            fn (): array => SightseeingObject::published()
                ->orderByDesc('published_at')
                ->limit(4)
                ->pluck('id')
                ->all(),
        );

        return $this->sortByCachedIds(
            SightseeingObject::published()
                ->with(['locality.voivodeship', 'objectTypes'])
                ->whereKey($latestObjectIds)
                ->get(),
            $latestObjectIds,
        );
    }

    private function latestNews(): Collection
    {
        $latestNewsIds = Cache::remember(
            'home:latest-news-ids',
            now()->addMinutes(5),
            fn (): array => Article::published()
                ->orderByDesc('published_at')
                ->limit(3)
                ->pluck('id')
                ->all(),
        );

        if (count($latestNewsIds) < 3) {
            return new Collection;
        }

        return $this->sortByCachedIds(
            Article::published()
                ->whereKey($latestNewsIds)
                ->get(),
            $latestNewsIds,
        );
    }

    /**
     * @param  array<int, int>  $ids
     */
    private function sortByCachedIds(Collection $models, array $ids): Collection
    {
        if ($ids === []) {
            return $models;
        }

        $positions = array_flip($ids);

        return $models
            ->sortBy(fn ($model): int => $positions[$model->getKey()] ?? PHP_INT_MAX)
            ->values();
    }
}
