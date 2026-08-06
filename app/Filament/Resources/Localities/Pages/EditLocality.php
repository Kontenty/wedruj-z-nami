<?php

namespace App\Filament\Resources\Localities\Pages;

use App\Filament\Resources\Localities\LocalityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLocality extends EditRecord
{
    protected static string $resource = LocalityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->visible(fn (): bool => auth()->user()?->isAdministrator() === true),
        ];
    }
}
