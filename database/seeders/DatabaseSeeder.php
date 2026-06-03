<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\User;
use App\Models\Voivodeship;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $voivodeships = collect([
            'dolnośląskie',
            'kujawsko-pomorskie',
            'lubelskie',
            'lubuskie',
            'łódzkie',
            'małopolskie',
            'mazowieckie',
            'opolskie',
            'podkarpackie',
            'podlaskie',
            'pomorskie',
            'śląskie',
            'świętokrzyskie',
            'warmińsko-mazurskie',
            'wielkopolskie',
            'zachodniopomorskie',
        ])->mapWithKeys(fn (string $name) => [
            $name => Voivodeship::query()->firstOrCreate(['name' => $name]),
        ]);

        $architecture = ObjectType::query()->firstOrCreate(['name' => 'Zabytki architektury']);
        $castlesAndFortifications = ObjectType::query()->firstOrCreate(['name' => 'Zamki i fortyfikacje'], ['parent_id' => $architecture->id]);
        $castles = ObjectType::query()->firstOrCreate(['name' => 'Zamki'], ['parent_id' => $castlesAndFortifications->id]);
        $religiousSites = ObjectType::query()->firstOrCreate(['name' => 'Obiekty sakralne'], ['parent_id' => $architecture->id]);

        $museums = ObjectType::query()->firstOrCreate(['name' => 'Muzea i skanseny']);
        $openAirMuseums = ObjectType::query()->firstOrCreate(['name' => 'Skanseny'], ['parent_id' => $museums->id]);

        $nature = ObjectType::query()->firstOrCreate(['name' => 'Obiekty przyrodnicze']);
        $nationalParks = ObjectType::query()->firstOrCreate(['name' => 'Parki narodowe'], ['parent_id' => $nature->id]);

        $memorials = ObjectType::query()->firstOrCreate(['name' => 'Pomniki i miejsca pamięci']);

        $this->createSightseeingObject([
            'title' => 'Wawel - Zamek Królewski',
            'lead' => 'Reprezentacyjny zespół zamkowy na wzgórzu wawelskim w Krakowie.',
            'description' => 'Wawel jest jednym z najważniejszych miejsc polskiej historii, łącząc funkcje dawnej rezydencji królewskiej, katedry oraz muzeum.',
            'locality' => 'Kraków',
            'is_unesco' => true,
            'opening_hours' => 'Godziny otwarcia zależne od sezonu i ekspozycji.',
            'ticket_prices' => 'Bilety zależne od wybranej trasy zwiedzania.',
            'accessibility' => 'Część ekspozycji dostępna dla osób z ograniczoną mobilnością.',
            'website' => 'https://wawel.krakow.pl',
            'latitude' => 50.0540,
            'longitude' => 19.9350,
            'geometry' => DB::raw("ST_GeomFromText('POINT(19.9350000 50.0540000)', 4326)"),
            'voivodeship_id' => $voivodeships['małopolskie']->id,
            'status' => 'published',
            'published' => true,
            'published_at' => now()->subDays(12),
            'data_source' => 'PTTK',
            'source_updated_at' => now()->subMonth(),
        ], [$castles->id]);

        $this->createSightseeingObject([
            'title' => 'Muzeum Zamoyskich w Kozłówce',
            'lead' => 'Zespół pałacowo-parkowy z zachowanym historycznym wyposażeniem.',
            'description' => 'Kozłówka prezentuje jedną z najlepiej zachowanych rezydencji arystokratycznych w Polsce.',
            'locality' => 'Kozłówka',
            'latitude' => 51.4492,
            'longitude' => 22.4909,
            'geometry' => DB::raw("ST_GeomFromText('POINT(22.4909000 51.4492000)', 4326)"),
            'voivodeship_id' => $voivodeships['lubelskie']->id,
            'status' => 'published',
            'published' => true,
            'published_at' => now()->subDays(8),
            'data_source' => 'PTTK',
            'source_updated_at' => now()->subWeeks(2),
        ], [$museums->id]);

        $this->createSightseeingObject([
            'title' => 'Białowieski Park Narodowy',
            'lead' => 'Najstarszy park narodowy w Polsce chroniący fragment Puszczy Białowieskiej.',
            'description' => 'Obszar parku obejmuje wyjątkowo cenne ekosystemy leśne oraz siedliska żubra.',
            'locality' => 'Białowieża',
            'latitude' => 52.7167,
            'longitude' => 23.8667,
            'geometry' => DB::raw("ST_GeomFromText('POLYGON((23.7800000 52.6500000,23.9500000 52.6500000,23.9500000 52.7900000,23.7800000 52.7900000,23.7800000 52.6500000))', 4326)"),
            'voivodeship_id' => $voivodeships['podlaskie']->id,
            'status' => 'published',
            'published' => true,
            'published_at' => now()->subDays(5),
            'data_source' => 'PTTK',
            'source_updated_at' => now()->subWeeks(3),
        ], [$nationalParks->id]);

        $this->createSightseeingObject([
            'title' => 'Jasna Góra',
            'lead' => 'Zespół klasztorny paulinów i jedno z najważniejszych sanktuariów w Polsce.',
            'description' => 'Jasna Góra jest ważnym miejscem pielgrzymkowym, historycznym i kulturowym.',
            'locality' => 'Częstochowa',
            'latitude' => 50.8120,
            'longitude' => 19.0970,
            'geometry' => DB::raw("ST_GeomFromText('POINT(19.0970000 50.8120000)', 4326)"),
            'voivodeship_id' => $voivodeships['śląskie']->id,
            'status' => 'published',
            'published' => true,
            'published_at' => now()->subDays(3),
            'data_source' => 'PTTK',
            'source_updated_at' => now()->subDays(20),
        ], [$religiousSites->id]);

        $this->createSightseeingObject([
            'title' => 'Westerplatte',
            'lead' => 'Historyczne miejsce pamięci związane z początkiem II wojny światowej.',
            'description' => 'Westerplatte pełni funkcję miejsca pamięci oraz ważnego punktu edukacji historycznej.',
            'locality' => 'Gdańsk',
            'latitude' => 54.4077,
            'longitude' => 18.6717,
            'geometry' => DB::raw("ST_GeomFromText('POINT(18.6717000 54.4077000)', 4326)"),
            'voivodeship_id' => $voivodeships['pomorskie']->id,
            'status' => 'draft',
            'published' => false,
            'published_at' => null,
            'data_source' => 'PTTK',
            'source_updated_at' => now()->subDays(10),
        ], [$memorials->id]);

        Article::query()->firstOrCreate([
            'title' => 'Nowe obiekty w katalogu PTTK',
        ], [
            'excerpt' => 'Katalog został uzupełniony o kolejne miejsca o znaczeniu krajoznawczym.',
            'body' => 'Redakcja PTTK systematycznie rozwija bazę obiektów krajoznawczych dostępnych dla turystów, nauczycieli i przewodników.',
            'status' => 'published',
            'published' => true,
            'published_at' => now()->subDays(4),
        ]);

        Article::query()->firstOrCreate([
            'title' => 'Prace nad mapą obiektów krajoznawczych',
        ], [
            'excerpt' => 'Trwają przygotowania do publicznej mapy obiektów krajoznawczych w Polsce.',
            'body' => 'Mapa będzie wspierać odkrywanie obiektów według lokalizacji, typu oraz oznaczenia UNESCO.',
            'status' => 'draft',
            'published' => false,
            'published_at' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, int>  $objectTypeIds
     */
    private function createSightseeingObject(array $attributes, array $objectTypeIds): void
    {
        $object = SightseeingObject::query()->updateOrCreate(
            ['title' => $attributes['title']],
            $attributes,
        );

        if ($object->wasRecentlyCreated) {
            $object->objectTypes()->sync($objectTypeIds);

            return;
        }

        $object->objectTypes()->sync($objectTypeIds);
    }
}
