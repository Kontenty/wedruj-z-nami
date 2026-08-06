<?php

namespace App\Filament\Resources\SightseeingObjects;

use App\Filament\Resources\SightseeingObjects\Pages\CreateSightseeingObject;
use App\Filament\Resources\SightseeingObjects\Pages\EditSightseeingObject;
use App\Filament\Resources\SightseeingObjects\Pages\ListSightseeingObjects;
use App\Filament\Resources\SightseeingObjects\Schemas\SightseeingObjectForm;
use App\Filament\Resources\SightseeingObjects\Tables\SightseeingObjectsTable;
use App\Models\SightseeingObject;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SightseeingObjectResource extends Resource
{
    protected static ?string $model = SightseeingObject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'obiekt krajoznawczy';

    protected static ?string $pluralModelLabel = 'obiekty krajoznawcze';

    protected static ?string $navigationLabel = 'Obiekty';

    protected static string|UnitEnum|null $navigationGroup = 'Treści';

    protected static ?int $navigationSort = 10;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['author', 'objectTypes', 'locality.voivodeship', 'media']);
    }

    public static function form(Schema $schema): Schema
    {
        return SightseeingObjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SightseeingObjectsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSightseeingObjects::route('/'),
            'create' => CreateSightseeingObject::route('/create'),
            'edit' => EditSightseeingObject::route('/{record}/edit'),
        ];
    }
}
