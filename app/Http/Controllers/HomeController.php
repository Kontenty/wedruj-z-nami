<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\SightseeingObject;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $latestObjects = SightseeingObject::published()
            ->with(['voivodeship', 'objectTypes'])
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        $latestNews = Article::published()
            ->limit(3)
            ->get();

        return view('home', compact('latestObjects', 'latestNews'));
    }
}
