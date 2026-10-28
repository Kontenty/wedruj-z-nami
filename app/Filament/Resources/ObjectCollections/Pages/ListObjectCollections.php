<?php

namespace App\Filament\Resources\ObjectCollections\Pages;

use App\Filament\Resources\ObjectCollections\ObjectCollectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListObjectCollections extends ListRecords
{
    protected static string $resource = ObjectCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
