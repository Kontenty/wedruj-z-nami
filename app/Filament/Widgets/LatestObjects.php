<?php

namespace App\Filament\Widgets;

use App\Models\SightseeingObject;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestObjects extends TableWidget
{
    protected static ?int $sort = 20;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Ostatnio aktualizowane obiekty')
            ->query(
                SightseeingObject::query()
                    ->with(['voivodeship', 'author'])
                    ->latest('updated_at')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Nazwa')
                    ->searchable(),
                TextColumn::make('voivodeship.name')
                    ->label('Województwo'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'published' ? 'Opublikowany' : 'Szkic')
                    ->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray'),
                IconColumn::make('is_unesco')
                    ->label('UNESCO')
                    ->boolean(),
                TextColumn::make('author.name')
                    ->label('Autor'),
                TextColumn::make('updated_at')
                    ->label('Aktualizacja')
                    ->dateTime('Y-m-d H:i'),
            ])
            ->paginated(false);
    }
}
