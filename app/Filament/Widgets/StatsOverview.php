<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\SightseeingObject;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 10;

    protected ?string $heading = 'Stan treści';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        return [
            Stat::make('Obiekty', SightseeingObject::query()->count())
                ->description('Szkice: '.SightseeingObject::query()->where('status', 'draft')->count().' / Opublikowane: '.SightseeingObject::query()->where('status', 'published')->count())
                ->icon(Heroicon::OutlinedMapPin)
                ->color('primary'),
            Stat::make('Aktualności', Article::query()->count())
                ->description('Szkice: '.Article::query()->where('status', 'draft')->count().' / Opublikowane: '.Article::query()->where('status', 'published')->count())
                ->icon(Heroicon::OutlinedNewspaper)
                ->color('success'),
            Stat::make('Archiwum aktualności', Article::query()->where('status', 'archived')->count())
                ->description('Wyróżnione: '.Article::query()->where('is_featured', true)->count())
                ->icon(Heroicon::OutlinedArchiveBox)
                ->color('warning'),
        ];
    }
}
