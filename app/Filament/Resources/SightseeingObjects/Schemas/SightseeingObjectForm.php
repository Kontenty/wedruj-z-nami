<?php

namespace App\Filament\Resources\SightseeingObjects\Schemas;

use App\Models\Locality;
use App\Models\SightseeingObject;
use App\Services\NominatimService;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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
                        MarkdownEditor::make('description')
                            ->label('Pełny opis')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Klasyfikacja i publikacja')
                    ->schema([
                        Select::make('locality_id')
                            ->label('Miejscowość')
                            ->relationship('locality', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                $locality = $state ? Locality::with('voivodeship')->find($state) : null;
                                $set('voivodeship_name', $locality?->voivodeship?->name ?? '');
                            }),
                        TextInput::make('voivodeship_name')
                            ->label('Województwo')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (TextInput $component, ?SightseeingObject $record, Set $set): void {
                                if ($record === null) {
                                    return;
                                }

                                $locality = $record->locality;
                                $component->state($locality?->voivodeship?->name ?? '');
                            }),
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
                            ->helperText('Format WKT: POLYGON((...)) lub MULTIPOLYGON(((...)),((...)))')
                            ->rows(4)
                            ->rules(fn (): array => [
                                'regex:/^(POLYGON|MULTIPOLYGON)\\s*\\(.+\\)$/i',
                                function (string $attribute, mixed $value, \Closure $fail): void {
                                    if (blank($value)) {
                                        return;
                                    }

                                    if (! self::isValidPolygonWkt((string) $value)) {
                                        $fail('Podaj poprawny poligon WKT.');
                                    }
                                },
                            ])
                            ->required(fn (Get $get): bool => $get('geometry_type') === 'polygon')
                            ->hidden(fn (Get $get): bool => $get('geometry_type') !== 'polygon')
                            ->hintAction(
                                Action::make('fetchPolygon')
                                    ->label('Pobierz z OSM')
                                    ->icon(Heroicon::GlobeAlt)
                                    ->requiresConfirmation()
                                    ->modalHeading('Pobierz poligon z OpenStreetMap')
                                    ->modalDescription(fn (Get $get): string => 'Szukam granicy: '.($get('title') ?? ''))
                                    ->action(function (Get $get, Set $set, NominatimService $nominatim): void {
                                        $title = $get('title');

                                        if (blank($title)) {
                                            return;
                                        }

                                        $result = $nominatim->searchPolygon($title);

                                        if ($result === null) {
                                            Notification::make()
                                                ->warning()
                                                ->title('Nie znaleziono poligonu')
                                                ->description('Nie udało się znaleźć granicy dla: '.$title)
                                                ->send();

                                            return;
                                        }

                                        $set('polygon_wkt', $result['wkt']);
                                        $set('osm_geometry_wkt', $result['wkt']);
                                        $set('osm_id', $result['osm_id']);
                                        $set('osm_type', $result['osm_type']);
                                        $set('geometry_type', 'polygon');
                                    })
                            )
                            ->columnSpanFull(),
                        TextInput::make('osm_geometry_wkt')
                            ->hidden(),
                        TextInput::make('osm_id')
                            ->hidden(),
                        TextInput::make('osm_type')
                            ->hidden(),
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
                            ->afterStateHydrated(function (FileUpload $component, ?SightseeingObject $record): void {
                                if ($record === null) {
                                    return;
                                }

                                $paths = $record
                                    ->getMedia('images')
                                    ->map(fn ($media) => $media->getPathRelativeToRoot())
                                    ->all();

                                if ($paths !== []) {
                                    $component->state($paths);
                                }
                            })
                            ->required(fn (Get $get): bool => $get('status') === 'published')
                            ->rules(fn (Get $get): array => [
                                function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                    $images = array_values(array_filter((array) $value));

                                    if ($get('status') === 'published' && $images === []) {
                                        $fail('Opublikowany obiekt musi mieć co najmniej jedno zdjęcie.');
                                    }
                                },
                            ])
                            ->helperText('Pierwsze zdjęcie w kolejności jest traktowane jako główne.'),
                        TextInput::make('image_author')
                            ->label('Autor zdjęć')
                            ->maxLength(255)
                            ->helperText('Autorsko nowo dodanych zdjęć.'),
                        TextInput::make('image_source')
                            ->label('Źródło zdjęć')
                            ->maxLength(255)
                            ->helperText('Źródło nowo dodanych zdjęć.'),
                        TextInput::make('image_alt')
                            ->label('Tekst alternatywny zdjęć')
                            ->helperText('Domyślny opis alt dla nowo dodanych zdjęć. Istniejące zdjęcia zachowują swoje metadane.')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    private static function isValidPolygonWkt(string $value): bool
    {
        $value = trim($value);

        if (preg_match('/^POLYGON\s*(\(.+\))$/i', $value, $matches) === 1) {
            return self::isValidPolygonBody(substr($matches[1], 1, -1));
        }

        if (preg_match('/^MULTIPOLYGON\s*(\(.+\))$/i', $value, $matches) !== 1) {
            return false;
        }

        $polygonGroups = self::splitTopLevelGroups(substr($matches[1], 1, -1));

        if ($polygonGroups === []) {
            return false;
        }

        foreach ($polygonGroups as $polygonGroup) {
            $polygonBody = self::unwrapGroup($polygonGroup);

            if ($polygonBody === null || ! self::isValidPolygonBody($polygonBody)) {
                return false;
            }
        }

        return true;
    }

    private static function isValidPolygonBody(string $value): bool
    {
        $ringGroups = self::splitTopLevelGroups($value);

        if ($ringGroups === []) {
            return false;
        }

        foreach ($ringGroups as $ringGroup) {
            $ringBody = self::unwrapGroup($ringGroup);

            if ($ringBody === null || ! self::isValidRing($ringBody)) {
                return false;
            }
        }

        return true;
    }

    private static function isValidRing(string $value): bool
    {
        $points = array_map(
            static fn (string $point): string => trim($point),
            explode(',', $value),
        );

        if (count($points) < 4) {
            return false;
        }

        $parsedPoints = [];

        foreach ($points as $point) {
            if (! preg_match('/^(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)$/', $point, $coordinates)) {
                return false;
            }

            $parsedPoints[] = [(float) $coordinates[1], (float) $coordinates[2]];
        }

        $firstPoint = $parsedPoints[0];
        $lastPoint = $parsedPoints[array_key_last($parsedPoints)];

        if ($firstPoint !== $lastPoint) {
            return false;
        }

        $uniquePoints = array_unique(
            array_map(
                static fn (array $point): string => implode(':', $point),
                array_slice($parsedPoints, 0, -1),
            ),
        );

        return count($uniquePoints) >= 3;
    }

    /**
     * @return list<string>
     */
    private static function splitTopLevelGroups(string $value): array
    {
        $groups = [];
        $buffer = '';
        $depth = 0;

        foreach (str_split($value) as $character) {
            if ($character === ',' && $depth === 0) {
                $group = trim($buffer);

                if ($group === '') {
                    return [];
                }

                $groups[] = $group;
                $buffer = '';

                continue;
            }

            if ($character === '(') {
                $depth++;
            }

            if ($character === ')') {
                $depth--;

                if ($depth < 0) {
                    return [];
                }
            }

            $buffer .= $character;
        }

        if ($depth !== 0) {
            return [];
        }

        $group = trim($buffer);

        if ($group === '') {
            return [];
        }

        $groups[] = $group;

        return $groups;
    }

    private static function unwrapGroup(string $value): ?string
    {
        $value = trim($value);

        if (! str_starts_with($value, '(') || ! str_ends_with($value, ')')) {
            return null;
        }

        $depth = 0;
        $length = strlen($value);

        for ($index = 0; $index < $length; $index++) {
            $character = $value[$index];

            if ($character === '(') {
                $depth++;
            }

            if ($character === ')') {
                $depth--;

                if ($depth < 0) {
                    return null;
                }

                if ($depth === 0 && $index !== $length - 1) {
                    return null;
                }
            }
        }

        if ($depth !== 0) {
            return null;
        }

        return substr($value, 1, -1);
    }
}
