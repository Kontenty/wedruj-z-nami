<?php

namespace App\Filament\Resources\ObjectTypes;

use App\Filament\Resources\ObjectTypes\Pages\CreateObjectType;
use App\Filament\Resources\ObjectTypes\Pages\EditObjectType;
use App\Filament\Resources\ObjectTypes\Pages\ListObjectTypes;
use App\Filament\Resources\ObjectTypes\Schemas\ObjectTypeForm;
use App\Filament\Resources\ObjectTypes\Tables\ObjectTypesTable;
use App\Models\ObjectType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ObjectTypeResource extends Resource
{
    protected static ?string $model = ObjectType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'typ obiektu';

    protected static ?string $pluralModelLabel = 'typy obiektów';

    protected static ?string $navigationLabel = 'Typy obiektów';

    protected static string|UnitEnum|null $navigationGroup = 'Taksonomia';

    protected static ?int $navigationSort = 30;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('parent');
    }

    public static function form(Schema $schema): Schema
    {
        return ObjectTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ObjectTypesTable::configure($table);
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
            'index' => ListObjectTypes::route('/'),
            'create' => CreateObjectType::route('/create'),
            'edit' => EditObjectType::route('/{record}/edit'),
        ];
    }
}
