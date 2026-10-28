<?php

namespace App\Filament\Resources\ObjectCollections\Schemas;

use App\Models\SightseeingObject;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ObjectCollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kolekcja')
                    ->schema([
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
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Klasyfikacja i obiekty')
                    ->schema([
                        CheckboxList::make('objectTypes')
                            ->label('Typy obiektów')
                            ->relationship('objectTypes', 'name')
                            ->columns(2)
                            ->columnSpanFull(),
                        Repeater::make('members')
                            ->label('Obiekty w kolekcji')
                            ->schema([
                                Select::make('sightseeing_object_id')
                                    ->label('Obiekt krajoznawczy')
                                    ->options(fn (): array => SightseeingObject::query()
                                        ->orderBy('title')
                                        ->pluck('title', 'id')
                                        ->all())
                                    ->searchable()
                                    ->required(),
                            ])
                            ->reorderable()
                            ->addActionLabel('Dodaj obiekt')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
