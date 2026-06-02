<?php

namespace App\Filament\Resources\SightseeingObjects\Pages;

use App\Filament\Resources\SightseeingObjects\SightseeingObjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSightseeingObjects extends ListRecords
{
    protected static string $resource = SightseeingObjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
