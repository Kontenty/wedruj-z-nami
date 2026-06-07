<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\User;
use App\Models\Voivodeship;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => 'password',
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

        $architecture = $this->ensureObjectType('Zabytki architektury');
        $castlesAndFortifications = $this->ensureObjectType('Zamki i fortyfikacje', $architecture);
        $castles = $this->ensureObjectType('Zamki', $castlesAndFortifications);
        $religiousSites = $this->ensureObjectType('Obiekty sakralne', $architecture);

        $museums = $this->ensureObjectType('Muzea i skanseny');
        $openAirMuseums = $this->ensureObjectType('Skanseny', $museums);

        $nature = $this->ensureObjectType('Obiekty przyrodnicze');
        $nationalParks = $this->ensureObjectType('Parki narodowe', $nature);

        $memorials = $this->ensureObjectType('Pomniki i miejsca pamięci');

        $wawel = $this->createSightseeingObject([
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

        $this->attachImages($wawel, 'wawel', 'Wawel - Zamek Królewski');

        $kozlowka = $this->createSightseeingObject([
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

        $this->attachImages($kozlowka, 'kozlowka', 'Muzeum Zamoyskich w Kozłówce');

        $bialowieza = $this->createSightseeingObject([
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

        $this->attachImages($bialowieza, 'bialowieza', 'Białowieski Park Narodowy');

        $biebrza = $this->createSightseeingObject([
            'title' => 'Biebrzański Park Narodowy',
            'lead' => 'Największy park narodowy w Polsce chroniący rozległe bagna i dolinę Biebrzy.',
            'description' => 'Park obejmuje cenne torfowiska, rozlewiska i siedliska ptaków wodno-błotnych.',
            'locality' => 'Osowiec-Twierdza',
            'latitude' => 53.5000,
            'longitude' => 22.8000,
            'geometry' => DB::raw("ST_GeomFromText('POLYGON((22.4500000 53.3000000,23.0500000 53.3000000,23.0500000 53.7000000,22.4500000 53.7000000,22.4500000 53.3000000))', 4326)"),
            'voivodeship_id' => $voivodeships['podlaskie']->id,
            'status' => 'published',
            'published' => true,
            'published_at' => now()->subDays(6),
            'data_source' => 'PTTK',
            'source_updated_at' => now()->subWeeks(3),
        ], [$nationalParks->id]);

        $this->attachImages($biebrza, 'biebrza', 'Biebrzański Park Narodowy');

        $jasnaGora = $this->createSightseeingObject([
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

        $this->attachImages($jasnaGora, 'jasna-gora', 'Jasna Góra');

        $westerplatte = $this->createSightseeingObject([
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

        $this->attachImages($westerplatte, 'westerplatte', 'Westerplatte');

        foreach ([
            [
                'attributes' => [
                    'title' => 'Zamek Cesarski w Poznaniu',
                    'lead' => 'Monumentalna rezydencja cesarska i jeden z symboli Poznania.',
                    'description' => 'Zamek Cesarski powstał na początku XX wieku jako ostatnia cesarska rezydencja w Europie i dziś pełni funkcje kulturalne.',
                    'locality' => 'Poznań',
                    'latitude' => 52.4087,
                    'longitude' => 16.9283,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(16.9283000 52.4087000)', 4326)"),
                    'voivodeship_id' => $voivodeships['wielkopolskie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(2),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(18),
                ],
                'objectTypeIds' => [$architecture->id],
            ],
            [
                'attributes' => [
                    'title' => 'Bazylika Archikatedralna św. Piotra i Pawła w Poznaniu',
                    'lead' => 'Najstarsza polska katedra i nekropolia pierwszych władców.',
                    'description' => 'Katedra na Ostrowie Tumskim jest jednym z najważniejszych zabytków sakralnych w Polsce.',
                    'locality' => 'Poznań',
                    'latitude' => 52.4215,
                    'longitude' => 16.9470,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(16.9470000 52.4215000)', 4326)"),
                    'voivodeship_id' => $voivodeships['wielkopolskie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(3),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(19),
                ],
                'objectTypeIds' => [$religiousSites->id],
            ],
            [
                'attributes' => [
                    'title' => 'Muzeum Narodowe w Poznaniu',
                    'lead' => 'Jedna z najważniejszych kolekcji sztuki w kraju.',
                    'description' => 'Muzeum Narodowe w Poznaniu prezentuje bogate zbiory malarstwa, rzeźby i rzemiosła artystycznego.',
                    'locality' => 'Poznań',
                    'latitude' => 52.4063,
                    'longitude' => 16.9242,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(16.9242000 52.4063000)', 4326)"),
                    'voivodeship_id' => $voivodeships['wielkopolskie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(4),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(20),
                ],
                'objectTypeIds' => [$museums->id],
            ],
            [
                'attributes' => [
                    'title' => 'Zamek Królewski w Warszawie',
                    'lead' => 'Rezydencja królewska i ważny symbol państwowości.',
                    'description' => 'Odbudowany po zniszczeniach wojennych zamek jest jednym z najważniejszych zabytków Warszawy.',
                    'locality' => 'Warszawa',
                    'latitude' => 52.2477,
                    'longitude' => 21.0137,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(21.0137000 52.2477000)', 4326)"),
                    'voivodeship_id' => $voivodeships['mazowieckie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(5),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(21),
                ],
                'objectTypeIds' => [$castles->id],
            ],
            [
                'attributes' => [
                    'title' => 'Pałac Kultury i Nauki',
                    'lead' => 'Ikona powojennej Warszawy i najwyższy zabytkowy wieżowiec w Polsce.',
                    'description' => 'PKiN jest jednym z najbardziej rozpoznawalnych punktów na mapie Warszawy i ważnym centrum wydarzeń.',
                    'locality' => 'Warszawa',
                    'latitude' => 52.2318,
                    'longitude' => 21.0067,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(21.0067000 52.2318000)', 4326)"),
                    'voivodeship_id' => $voivodeships['mazowieckie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(6),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(22),
                ],
                'objectTypeIds' => [$architecture->id],
            ],
            [
                'attributes' => [
                    'title' => 'Muzeum Powstania Warszawskiego',
                    'lead' => 'Nowoczesne muzeum upamiętniające powstańców Warszawy.',
                    'description' => 'Ekspozycja muzeum opowiada o przebiegu i znaczeniu Powstania Warszawskiego.',
                    'locality' => 'Warszawa',
                    'latitude' => 52.2328,
                    'longitude' => 20.9818,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(20.9818000 52.2328000)', 4326)"),
                    'voivodeship_id' => $voivodeships['mazowieckie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(7),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(23),
                ],
                'objectTypeIds' => [$museums->id],
            ],
            [
                'attributes' => [
                    'title' => 'Pomnik Bohaterów Getta',
                    'lead' => 'Najważniejsze warszawskie miejsce pamięci o powstaniu w getcie.',
                    'description' => 'Pomnik upamiętnia bohaterów powstania w getcie warszawskim i jest jednym z kluczowych miejsc pamięci stolicy.',
                    'locality' => 'Warszawa',
                    'latitude' => 52.2497,
                    'longitude' => 20.9964,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(20.9964000 52.2497000)', 4326)"),
                    'voivodeship_id' => $voivodeships['mazowieckie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(8),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(24),
                ],
                'objectTypeIds' => [$memorials->id],
            ],
            [
                'attributes' => [
                    'title' => 'Zamek w Malborku',
                    'lead' => 'Największy ceglany zamek na świecie i wpis UNESCO.',
                    'description' => 'Potężny zespół zamkowy Zakonu Krzyżackiego należy do najważniejszych zabytków średniowiecznej Europy.',
                    'locality' => 'Malbork',
                    'is_unesco' => true,
                    'latitude' => 54.0377,
                    'longitude' => 19.0264,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(19.0264000 54.0377000)', 4326)"),
                    'voivodeship_id' => $voivodeships['pomorskie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(9),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(25),
                ],
                'objectTypeIds' => [$castles->id],
            ],
            [
                'attributes' => [
                    'title' => 'Kopalnia Soli w Wieliczce',
                    'lead' => 'Legendarna kopalnia soli i jeden z najbardziej znanych obiektów UNESCO w Polsce.',
                    'description' => 'Podziemny kompleks tras turystycznych i kaplic zachwyca skalą oraz historią górnictwa solnego.',
                    'locality' => 'Wieliczka',
                    'is_unesco' => true,
                    'latitude' => 49.9831,
                    'longitude' => 20.0600,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(20.0600000 49.9831000)', 4326)"),
                    'voivodeship_id' => $voivodeships['małopolskie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(10),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(26),
                ],
                'objectTypeIds' => [$architecture->id],
            ],
            [
                'attributes' => [
                    'title' => 'Stare Miasto w Krakowie',
                    'lead' => 'Historyczne serce Krakowa i jedno z najcenniejszych założeń urbanistycznych w Europie.',
                    'description' => 'Zabytkowe centrum Krakowa łączy średniowieczny układ ulic z najważniejszymi zabytkami miasta.',
                    'locality' => 'Kraków',
                    'is_unesco' => true,
                    'latitude' => 50.0619,
                    'longitude' => 19.9372,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(19.9372000 50.0619000)', 4326)"),
                    'voivodeship_id' => $voivodeships['małopolskie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(11),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(27),
                ],
                'objectTypeIds' => [$architecture->id],
            ],
            [
                'attributes' => [
                    'title' => 'Bazylika Mariacka w Gdańsku',
                    'lead' => 'Gotycka świątynia dominująca nad panoramą Gdańska.',
                    'description' => 'Bazylika Mariacka jest jedną z największych ceglastych świątyń na świecie i ważnym zabytkiem sakralnym.',
                    'locality' => 'Gdańsk',
                    'latitude' => 54.3489,
                    'longitude' => 18.6470,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(18.6470000 54.3489000)', 4326)"),
                    'voivodeship_id' => $voivodeships['pomorskie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(12),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(28),
                ],
                'objectTypeIds' => [$religiousSites->id],
            ],
            [
                'attributes' => [
                    'title' => 'Skansen w Sanoku',
                    'lead' => 'Największy skansen w Polsce i ważna lekcja kultury Pogórza i Bieszczadów.',
                    'description' => 'Muzeum Budownictwa Ludowego w Sanoku prezentuje architekturę i życie codzienne dawnych mieszkańców regionu.',
                    'locality' => 'Sanok',
                    'latitude' => 49.5600,
                    'longitude' => 22.2062,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(22.2062000 49.5600000)', 4326)"),
                    'voivodeship_id' => $voivodeships['podkarpackie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(13),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(29),
                ],
                'objectTypeIds' => [$openAirMuseums->id],
            ],
            [
                'attributes' => [
                    'title' => 'Muzeum Auschwitz-Birkenau',
                    'lead' => 'Miejsce pamięci i muzeum upamiętniające ofiary obozu Auschwitz.',
                    'description' => 'Były niemiecki nazistowski obóz koncentracyjny i zagłady jest jednym z najważniejszych miejsc pamięci na świecie.',
                    'locality' => 'Oświęcim',
                    'latitude' => 50.0340,
                    'longitude' => 19.1785,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(19.1785000 50.0340000)', 4326)"),
                    'voivodeship_id' => $voivodeships['małopolskie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(14),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(30),
                ],
                'objectTypeIds' => [$memorials->id],
            ],
            [
                'attributes' => [
                    'title' => 'Święta Lipka',
                    'lead' => 'Barokowe sanktuarium pielgrzymkowe na Mazurach.',
                    'description' => 'Kompleks kościelno-klasztorny słynie z architektury, organów i roli w dziejach religijnych regionu.',
                    'locality' => 'Święta Lipka',
                    'latitude' => 54.0192,
                    'longitude' => 21.2261,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(21.2261000 54.0192000)', 4326)"),
                    'voivodeship_id' => $voivodeships['warmińsko-mazurskie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(15),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(31),
                ],
                'objectTypeIds' => [$religiousSites->id],
            ],
            [
                'attributes' => [
                    'title' => 'Kopalnia Guido w Zabrzu',
                    'lead' => 'Historyczna kopalnia węgla z jedną z najciekawszych tras podziemnych w Polsce.',
                    'description' => 'Guido pokazuje dziedzictwo przemysłowe Górnego Śląska i umożliwia zwiedzanie pod ziemią.',
                    'locality' => 'Zabrze',
                    'latitude' => 50.3051,
                    'longitude' => 18.7835,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(18.7835000 50.3051000)', 4326)"),
                    'voivodeship_id' => $voivodeships['śląskie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(16),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(32),
                ],
                'objectTypeIds' => [$museums->id],
            ],
            [
                'attributes' => [
                    'title' => 'Hala Stulecia we Wrocławiu',
                    'lead' => 'Ikona modernistycznej architektury i ważny punkt Wrocławia.',
                    'description' => 'Monumentalna hala wpisana na listę UNESCO jest jednym z najważniejszych obiektów architektonicznych miasta.',
                    'locality' => 'Wrocław',
                    'is_unesco' => true,
                    'latitude' => 51.1067,
                    'longitude' => 17.0770,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(17.0770000 51.1067000)', 4326)"),
                    'voivodeship_id' => $voivodeships['dolnośląskie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(17),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(33),
                ],
                'objectTypeIds' => [$architecture->id],
            ],
            [
                'attributes' => [
                    'title' => 'Bieszczadzki Park Narodowy',
                    'lead' => 'Dzika część Karpat z połoninami i rozległymi lasami.',
                    'description' => 'Park chroni najbardziej naturalne fragmenty polskich Bieszczadów i jest rajem dla miłośników wędrówek.',
                    'locality' => 'Ustrzyki Górne',
                    'latitude' => 49.1502,
                    'longitude' => 22.6200,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(22.6200000 49.1502000)', 4326)"),
                    'voivodeship_id' => $voivodeships['podkarpackie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(18),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(34),
                ],
                'objectTypeIds' => [$nationalParks->id],
            ],
            [
                'attributes' => [
                    'title' => 'Zamek w Łańcucie',
                    'lead' => 'Rezydencja magnacka znana z pięknych wnętrz i ogrodów.',
                    'description' => 'Zespół zamkowo-parkowy w Łańcucie należy do najcenniejszych rezydencji arystokratycznych w Polsce.',
                    'locality' => 'Łańcut',
                    'latitude' => 50.0687,
                    'longitude' => 22.2320,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(22.2320000 50.0687000)', 4326)"),
                    'voivodeship_id' => $voivodeships['podkarpackie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(19),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(35),
                ],
                'objectTypeIds' => [$castles->id],
            ],
            [
                'attributes' => [
                    'title' => 'Zamek w Kórniku',
                    'lead' => 'Neogotycka rezydencja nad jeziorem i jeden z symboli Kórnika.',
                    'description' => 'Zamek w Kórniku łączy funkcje rezydencji i muzeum, a jego otoczenie tworzy rozpoznawalny krajobraz Wielkopolski.',
                    'locality' => 'Kórnik',
                    'latitude' => 52.2400,
                    'longitude' => 17.0899,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(17.0899000 52.2400000)', 4326)"),
                    'voivodeship_id' => $voivodeships['wielkopolskie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(20),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(36),
                ],
                'objectTypeIds' => [$castles->id],
            ],
            [
                'attributes' => [
                    'title' => 'Muzeum Wsi Opolskiej',
                    'lead' => 'Skansen prezentujący architekturę i kulturę regionu opolskiego.',
                    'description' => 'Muzeum na wolnym powietrzu pokazuje zabytkowe budownictwo wiejskie i tradycje dawnego Śląska Opolskiego.',
                    'locality' => 'Opole',
                    'latitude' => 50.6827,
                    'longitude' => 17.9207,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(17.9207000 50.6827000)', 4326)"),
                    'voivodeship_id' => $voivodeships['opolskie']->id,
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(21),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(37),
                ],
                'objectTypeIds' => [$openAirMuseums->id],
            ],
        ] as $definition) {
            $this->createSightseeingObject($definition['attributes'], $definition['objectTypeIds']);
        }

        Article::query()->updateOrCreate([
            'title' => 'Nowe obiekty w katalogu PTTK',
        ], [
            'excerpt' => 'Katalog został uzupełniony o kolejne miejsca o znaczeniu krajoznawczym.',
            'body' => 'Redakcja PTTK systematycznie rozwija bazę obiektów krajoznawczych dostępnych dla turystów, nauczycieli i przewodników.',
            'status' => 'published',
            'published' => true,
            'published_at' => now()->subDays(4),
        ]);

        Article::query()->updateOrCreate([
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
    private function createSightseeingObject(array $attributes, array $objectTypeIds): SightseeingObject
    {
        $object = SightseeingObject::query()->updateOrCreate(
            ['title' => $attributes['title']],
            $attributes,
        );

        $object->objectTypes()->sync($objectTypeIds);

        return $object;
    }

    /**
     * Attach images to a sightseeing object from the public images directory.
     *
     * Images are stored locally under public/images/{directory} and sourced from Wikimedia Commons.
     *
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    private function attachImages(SightseeingObject $object, string $directory, string $altText): void
    {
        $imagesPath = public_path('images/'.$directory);

        if (! is_dir($imagesPath)) {
            return;
        }

        $mediaProperties = ['alt' => $altText, 'author' => null, 'source' => 'Wikimedia Commons'];
        $imagePaths = [
            $imagesPath.'/main.jpg',
            $imagesPath.'/additional-1.jpg',
            $imagesPath.'/additional-2.jpg',
        ];
        $existingFileNames = $object->getMedia('images')
            ->pluck('file_name')
            ->all();

        foreach ($imagePaths as $imagePath) {
            $fileName = basename($imagePath);

            if (! file_exists($imagePath) || in_array($fileName, $existingFileNames, true)) {
                continue;
            }

            $object->addMedia($imagePath)
                ->withCustomProperties($mediaProperties)
                ->toMediaCollection('images');
        }
    }

    private function ensureObjectType(string $name, ?ObjectType $parent = null): ObjectType
    {
        return ObjectType::query()->updateOrCreate([
            'name' => $name,
        ], [
            'parent_id' => $parent?->id,
        ]);
    }
}
