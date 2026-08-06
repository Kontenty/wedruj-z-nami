<?php

namespace App\Filament\Resources\Localities\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LocalityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('voivodeship_id')
                    ->label('Województwo')
                    ->relationship('voivodeship', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->label('Nazwa')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Pozostaw puste, aby wygenerować automatycznie z nazwy.'),
                MarkdownEditor::make('description')
                    ->label('Opis')
                    ->columnSpanFull(),
            ]);
    }
}
