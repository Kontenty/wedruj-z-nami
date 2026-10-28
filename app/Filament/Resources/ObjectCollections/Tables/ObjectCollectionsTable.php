<?php

namespace App\Filament\Resources\ObjectCollections\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ObjectCollectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('objectTypes.name')
                    ->label('Typy obiektów')
                    ->badge()
                    ->separator(', '),
                TextColumn::make('sightseeing_objects_count')
                    ->label('Liczba obiektów')
                    ->counts('sightseeingObjects')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
