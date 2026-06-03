<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\SightseeingObject;
use Illuminate\Contracts\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        $news = Article::published()
            ->paginate(12);

        $latestObjects = SightseeingObject::published()
            ->with('voivodeship')
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return view('news.index', compact('news', 'latestObjects'));
    }

    public function show(string $slug): View
    {
        $newsItem = Article::where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        return view('news.show', compact('newsItem'));
    }
}
