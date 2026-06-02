<?php

namespace App\Filament\Resources\ObjectTypes\Pages;

use App\Filament\Resources\ObjectTypes\ObjectTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateObjectType extends CreateRecord
{
    protected static string $resource = ObjectTypeResource::class;
}
