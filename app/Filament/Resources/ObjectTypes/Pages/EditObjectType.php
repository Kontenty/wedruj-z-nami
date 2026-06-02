<?php

namespace App\Filament\Resources\ObjectTypes\Pages;

use App\Filament\Resources\ObjectTypes\ObjectTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditObjectType extends EditRecord
{
    protected static string $resource = ObjectTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->visible(fn (): bool => auth()->user()?->isAdministrator() === true),
        ];
    }
}
