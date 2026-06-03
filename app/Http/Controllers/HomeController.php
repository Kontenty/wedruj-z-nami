<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\SightseeingObject;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $latestObjects = $this->latestObjects();
        $latestNews = $this->latestNews();

        return view('home', compact('latestObjects', 'latestNews'));
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
                ->with(['voivodeship', 'objectTypes'])
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
                ->limit(3)
                ->pluck('id')
                ->all(),
        );

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
