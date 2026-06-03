<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\SightseeingObject;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $latestObjects = Cache::remember('home:latest-objects', now()->addMinutes(5), fn () => SightseeingObject::published()
            ->with(['voivodeship', 'objectTypes'])
            ->orderByDesc('published_at')
            ->limit(4)
            ->get());

        $latestNews = Cache::remember('home:latest-news', now()->addMinutes(5), fn () => Article::published()
            ->orderByDesc('published_at')
            ->limit(3)
            ->get());

        return view('home', compact('latestObjects', 'latestNews'));
    }
}
