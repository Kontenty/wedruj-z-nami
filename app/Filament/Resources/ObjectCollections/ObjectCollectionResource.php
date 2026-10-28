<?php

namespace App\Filament\Resources\ObjectCollections;

use App\Filament\Resources\ObjectCollections\Pages\CreateObjectCollection;
use App\Filament\Resources\ObjectCollections\Pages\EditObjectCollection;
use App\Filament\Resources\ObjectCollections\Pages\ListObjectCollections;
use App\Filament\Resources\ObjectCollections\Schemas\ObjectCollectionForm;
use App\Filament\Resources\ObjectCollections\Tables\ObjectCollectionsTable;
use App\Models\ObjectCollection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ObjectCollectionResource extends Resource
{
    protected static ?string $model = ObjectCollection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'kolekcja obiektów';

    protected static ?string $pluralModelLabel = 'kolekcje obiektów';

    protected static ?string $navigationLabel = 'Kolekcje';

    protected static string|UnitEnum|null $navigationGroup = 'Treści';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return ObjectCollectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ObjectCollectionsTable::configure($table);
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
            'index' => ListObjectCollections::route('/'),
            'create' => CreateObjectCollection::route('/create'),
            'edit' => EditObjectCollection::route('/{record}/edit'),
        ];
    }
}
