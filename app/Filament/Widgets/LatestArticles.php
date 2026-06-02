<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestArticles extends TableWidget
{
    protected static ?int $sort = 30;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Ostatnio aktualizowane aktualności')
            ->query(
                Article::query()
                    ->with('author')
                    ->latest('updated_at')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Tytuł')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Opublikowana',
                        'archived' => 'Zarchiwizowana',
                        default => 'Szkic',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'archived' => 'warning',
                        default => 'gray',
                    }),
                IconColumn::make('is_featured')
                    ->label('Wyróżniona')
                    ->boolean(),
                TextColumn::make('author.name')
                    ->label('Autor'),
                TextColumn::make('published_at')
                    ->label('Publikacja')
                    ->dateTime('Y-m-d H:i'),
                TextColumn::make('updated_at')
                    ->label('Aktualizacja')
                    ->dateTime('Y-m-d H:i'),
            ])
            ->paginated(false);
    }
}
