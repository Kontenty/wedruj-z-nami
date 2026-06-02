<?php

namespace App\Filament\Resources\SightseeingObjects\Schemas;

use App\Models\SightseeingObject;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class SightseeingObjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Treść')
                    ->schema([
                        TextInput::make('title')
                            ->label('Nazwa')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Pozostaw puste, aby wygenerować automatycznie z nazwy.'),
                        Textarea::make('lead')
                            ->label('Krótki opis')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Pełny opis')
                            ->required()
                            ->rows(8)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Klasyfikacja i publikacja')
                    ->schema([
                        Select::make('voivodeship_id')
                            ->label('Województwo')
                            ->relationship('voivodeship', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('locality')
                            ->label('Miejscowość')
                            ->required()
                            ->maxLength(255),
                        CheckboxList::make('objectTypes')
                            ->label('Typy obiektu')
                            ->relationship('objectTypes', 'name')
                            ->columns(2)
                            ->required()
                            ->columnSpanFull(),
                        Toggle::make('is_unesco')
                            ->label('Obiekt UNESCO'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Szkic',
                                'published' => 'Opublikowany',
                            ])
                            ->default('draft')
                            ->required()
                            ->rules([Rule::in(['draft', 'published'])]),
                        DateTimePicker::make('published_at')
                            ->label('Data publikacji')
                            ->seconds(false)
                            ->helperText('Dla publikacji bez daty system ustawi bieżący czas przy zapisie.'),
                    ])
                    ->columns(2),

                Section::make('Lokalizacja i geometria')
                    ->schema([
                        Select::make('geometry_type')
                            ->label('Typ geometrii')
                            ->options([
                                'point' => 'Punkt',
                                'polygon' => 'Poligon',
                            ])
                            ->default('point')
                            ->required()
                            ->live(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('latitude')
                                    ->label('Szerokość geograficzna')
                                    ->numeric()
                                    ->minValue(-90)
                                    ->maxValue(90)
                                    ->required(fn (Get $get): bool => $get('geometry_type') === 'point')
                                    ->hidden(fn (Get $get): bool => $get('geometry_type') !== 'point'),
                                TextInput::make('longitude')
                                    ->label('Długość geograficzna')
                                    ->numeric()
                                    ->minValue(-180)
                                    ->maxValue(180)
                                    ->required(fn (Get $get): bool => $get('geometry_type') === 'point')
                                    ->hidden(fn (Get $get): bool => $get('geometry_type') !== 'point'),
                            ]),
                        Textarea::make('polygon_wkt')
                            ->label('Poligon WKT')
                            ->helperText('Format: POLYGON((19.93 50.05, 19.96 50.05, 19.96 50.08, 19.93 50.05))')
                            ->rows(4)
                            ->rules(['regex:/^POLYGON\\s*\\(\\(.+\\)\\)$/i'])
                            ->required(fn (Get $get): bool => $get('geometry_type') === 'polygon')
                            ->hidden(fn (Get $get): bool => $get('geometry_type') !== 'polygon')
                            ->columnSpanFull(),
                    ]),

                Section::make('Informacje praktyczne')
                    ->schema([
                        Textarea::make('opening_hours')
                            ->label('Godziny otwarcia')
                            ->rows(3),
                        Textarea::make('ticket_prices')
                            ->label('Ceny biletów')
                            ->rows(3),
                        Textarea::make('accessibility')
                            ->label('Dostępność')
                            ->rows(3),
                        TextInput::make('website')
                            ->label('Strona internetowa')
                            ->url()
                            ->maxLength(500),
                    ])
                    ->columns(2),

                Section::make('Źródła')
                    ->schema([
                        TextInput::make('data_source')
                            ->label('Źródło danych')
                            ->maxLength(255),
                        DatePicker::make('source_updated_at')
                            ->label('Data aktualizacji źródła'),
                    ])
                    ->columns(2),

                Section::make('Galeria')
                    ->schema([
                        FileUpload::make('images')
                            ->label('Zdjęcia')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->disk('public')
                            ->directory('cms/object-images')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(10240)
                            ->required(fn (Get $get, ?SightseeingObject $record): bool => $get('status') === 'published' && ! $record?->hasMedia('images'))
                            ->rules(fn (Get $get, ?SightseeingObject $record): array => [
                                function (string $attribute, mixed $value, \Closure $fail) use ($get, $record): void {
                                    if ($get('status') === 'published' && blank($value) && ! $record?->hasMedia('images')) {
                                        $fail('Opublikowany obiekt musi mieć co najmniej jedno zdjęcie.');
                                    }
                                },
                            ])
                            ->helperText('Pierwsze zdjęcie w kolejności jest traktowane jako główne.'),
                        TextInput::make('image_author')
                            ->label('Autor zdjęć')
                            ->maxLength(255),
                        TextInput::make('image_source')
                            ->label('Źródło zdjęć')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}
