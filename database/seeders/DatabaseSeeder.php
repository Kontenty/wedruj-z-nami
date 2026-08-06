<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Locality;
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
            'role' => User::ROLE_ADMINISTRATOR,
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

        $localityData = [
            'Kraków' => ['małopolskie', 'Kraków — dawna stolica Polski, królewskie miasto pełne zabytków, katedr i muzeów. Stare Miasto z Sukiennicami i Wawelem należą do najcenniejszych zabytków w kraju.'],
            'Kozłówka' => ['lubelskie', 'Kozłówka — niewielka miejscowość w województwie lubelskim, znana z zabytkowego zespołu pałacowo-parkowego Zamoyskich.'],
            'Białowieża' => ['podlaskie', 'Białowieża — wieś na Podlasiu, serce Puszczy Białowieskiej i siedziba Białowieskiego Parku Narodowego.'],
            'Osowiec-Twierdza' => ['podlaskie', 'Osowiec-Twierdza — osada w dolinie Biebrzy, znana z fortyfikacji i bliskości Biebrzańskiego Parku Narodowego.'],
            'Częstochowa' => ['śląskie', 'Częstochowa — miasto na Jurze Krakowsko-Częstochowskiej, duchowa stolica Polski dzięki sanktuarium na Jasnej Górze.'],
            'Gdańsk' => ['pomorskie', 'Gdańsk — hanzeatyckie miasto portowe na Pomorzu, z bogatą historią, architekturą i dostępem do Morza Bałtyckiego.'],
            'Poznań' => ['wielkopolskie', 'Poznań — stolica Wielkopolski, jedno z najstarszych polskich miast z bogatą historią i zabytkami.'],
            'Warszawa' => ['mazowieckie', 'Warszawa — stolica Polski, dynamiczne miasto łączące historię z nowoczesnością.'],
            'Malbork' => ['pomorskie', 'Malbork — miasto na Pomorzu znane z potężnego zamku krzyżackiego, największego ceglanego zamku na świecie.'],
            'Wieliczka' => ['małopolskie', 'Wieliczka — miasto pod Krakowem słynące z zabytkowej kopalni soli, wpisanej na listę UNESCO.'],
            'Sanok' => ['podkarpackie', 'Sanok — miasto w Bieszczadach z największym skansenem w Polsce i bogatą historią.'],
            'Oświęcim' => ['małopolskie', 'Oświęcim — miasto w Małopolsce, miejsce pamięci byłego obozu Auschwitz-Birkenau.'],
            'Święta Lipka' => ['warmińsko-mazurskie', 'Święta Lipka — niewielka miejscowość na Mazurach z barokowym sanktuarium maryjnym.'],
            'Zabrze' => ['śląskie', 'Zabrze — miasto na Górnym Śląsku z bogatym dziedzictwem przemysłowym i zabytkami techniki.'],
            'Wrocław' => ['dolnośląskie', 'Wrocław — stolica Dolnego Śląska, miasto stu mostów z pięknym rynkiem i bogatą architekturą.'],
            'Ustrzyki Górne' => ['podkarpackie', 'Ustrzyki Górne — bieszczadzka miejscowość, brama do Bieszczadzkiego Parku Narodowego.'],
            'Łańcut' => ['podkarpackie', 'Łańcut — miasto na Podkarpaciu znane z pięknego zamku Lubomirskich i Potockich.'],
            'Kórnik' => ['wielkopolskie', 'Kórnik — miejscowość w Wielkopolsce z neogotyckim zamkiem i arboretum.'],
            'Opole' => ['opolskie', 'Opole — stolica województwa opolskiego, miasto z bogatą historią i tradycjami kulturalnymi.'],
        ];

        $localities = collect($localityData)->mapWithKeys(function (array $data, string $name) use ($voivodeships) {
            [$voivodeshipName, $description] = $data;

            return [
                $name => Locality::query()->firstOrCreate(
                    ['name' => $name],
                    ['description' => $description, 'voivodeship_id' => $voivodeships[$voivodeshipName]->id],
                ),
            ];
        });

        $pomnikiHistorii = $this->ensureObjectType('Pomniki Historii');
        $parkiNarodowe = $this->ensureObjectType('Parki narodowe');
        $glowneMiasta = $this->ensureObjectType('Główne miasta');
        $obiektySakralne = $this->ensureObjectType('Obiekty sakralne');
        $zamkiPalace = $this->ensureObjectType('Zamki i pałace');
        $zabytkiTechniki = $this->ensureObjectType('Zabytki techniki');
        $muzea = $this->ensureObjectType('Muzea');
        $skanseny = $this->ensureObjectType('Skanseny');
        $obiektyPrzyrodnicze = $this->ensureObjectType('Obiekty przyrodnicze');
        $polaBitew = $this->ensureObjectType('Pola bitew');

        $wawel = $this->createSightseeingObject([
            'title' => 'Wawel - Zamek Królewski',
            'lead' => 'Reprezentacyjny zespół zamkowy na wzgórzu wawelskim w Krakowie.',
            'description' => 'Wawel jest jednym z najważniejszych miejsc polskiej historii, łącząc funkcje dawnej rezydencji królewskiej, katedry oraz muzeum.',
            'locality_id' => $localities['Kraków']->id,
            'is_unesco' => true,
            'opening_hours' => 'Godziny otwarcia zależne od sezonu i ekspozycji.',
            'ticket_prices' => 'Bilety zależne od wybranej trasy zwiedzania.',
            'accessibility' => 'Część ekspozycji dostępna dla osób z ograniczoną mobilnością.',
            'website' => 'https://wawel.krakow.pl',
            'latitude' => 50.0540,
            'longitude' => 19.9350,
            'geometry' => DB::raw("ST_GeomFromText('POINT(19.9350000 50.0540000)', 4326)"),
            'status' => 'published',
            'published' => true,
            'published_at' => now()->subDays(12),
            'data_source' => 'PTTK',
            'source_updated_at' => now()->subMonth(),
        ], [$zamkiPalace->id]);

        $this->attachImages($wawel, 'wawel', 'Wawel - Zamek Królewski');

        $kozlowka = $this->createSightseeingObject([
            'title' => 'Muzeum Zamoyskich w Kozłówce',
            'lead' => 'Zespół pałacowo-parkowy z zachowanym historycznym wyposażeniem.',
            'description' => 'Kozłówka prezentuje jedną z najlepiej zachowanych rezydencji arystokratycznych w Polsce.',
            'locality_id' => $localities['Kozłówka']->id,
            'latitude' => 51.4492,
            'longitude' => 22.4909,
            'geometry' => DB::raw("ST_GeomFromText('POINT(22.4909000 51.4492000)', 4326)"),
            'status' => 'published',
            'published' => true,
            'published_at' => now()->subDays(8),
            'data_source' => 'PTTK',
            'source_updated_at' => now()->subWeeks(2),
        ], [$muzea->id]);

        $this->attachImages($kozlowka, 'kozlowka', 'Muzeum Zamoyskich w Kozłówce');

        $bialowieza = $this->createSightseeingObject([
            'title' => 'Białowieski Park Narodowy',
            'lead' => 'Najstarszy park narodowy w Polsce chroniący fragment Puszczy Białowieskiej.',
            'description' => 'Obszar parku obejmuje wyjątkowo cenne ekosystemy leśne oraz siedliska żubra.',
            'locality_id' => $localities['Białowieża']->id,
            'latitude' => 52.7167,
            'longitude' => 23.8667,
            'geometry' => DB::raw("ST_GeomFromText('POLYGON((23.7800000 52.6500000,23.9500000 52.6500000,23.9500000 52.7900000,23.7800000 52.7900000,23.7800000 52.6500000))', 4326)"),
            'status' => 'published',
            'published' => true,
            'published_at' => now()->subDays(5),
            'data_source' => 'PTTK',
            'source_updated_at' => now()->subWeeks(3),
        ], [$parkiNarodowe->id]);

        $this->attachImages($bialowieza, 'bialowieza', 'Białowieski Park Narodowy');

        $biebrza = $this->createSightseeingObject([
            'title' => 'Biebrzański Park Narodowy',
            'lead' => 'Największy park narodowy w Polsce chroniący rozległe bagna i dolinę Biebrzy.',
            'description' => 'Park obejmuje cenne torfowiska, rozlewiska i siedliska ptaków wodno-błotnych.',
            'locality_id' => $localities['Osowiec-Twierdza']->id,
            'latitude' => 53.5000,
            'longitude' => 22.8000,
            'geometry' => DB::raw("ST_GeomFromText('POLYGON((22.4500000 53.3000000,23.0500000 53.3000000,23.0500000 53.7000000,22.4500000 53.7000000,22.4500000 53.3000000))', 4326)"),
            'status' => 'published',
            'published' => true,
            'published_at' => now()->subDays(6),
            'data_source' => 'PTTK',
            'source_updated_at' => now()->subWeeks(3),
        ], [$parkiNarodowe->id]);

        $this->attachImages($biebrza, 'biebrza', 'Biebrzański Park Narodowy');

        $jasnaGora = $this->createSightseeingObject([
            'title' => 'Jasna Góra',
            'lead' => 'Zespół klasztorny paulinów i jedno z najważniejszych sanktuariów w Polsce.',
            'description' => 'Jasna Góra jest ważnym miejscem pielgrzymkowym, historycznym i kulturowym.',
            'locality_id' => $localities['Częstochowa']->id,
            'latitude' => 50.8120,
            'longitude' => 19.0970,
            'geometry' => DB::raw("ST_GeomFromText('POINT(19.0970000 50.8120000)', 4326)"),
            'status' => 'published',
            'published' => true,
            'published_at' => now()->subDays(3),
            'data_source' => 'PTTK',
            'source_updated_at' => now()->subDays(20),
        ], [$obiektySakralne->id]);

        $this->attachImages($jasnaGora, 'jasna-gora', 'Jasna Góra');

        $westerplatte = $this->createSightseeingObject([
            'title' => 'Westerplatte',
            'lead' => 'Historyczne miejsce pamięci związane z początkiem II wojny światowej.',
            'description' => 'Westerplatte pełni funkcję miejsca pamięci oraz ważnego punktu edukacji historycznej.',
            'locality_id' => $localities['Gdańsk']->id,
            'latitude' => 54.4077,
            'longitude' => 18.6717,
            'geometry' => DB::raw("ST_GeomFromText('POINT(18.6717000 54.4077000)', 4326)"),
            'status' => 'draft',
            'published' => false,
            'published_at' => null,
            'data_source' => 'PTTK',
            'source_updated_at' => now()->subDays(10),
        ], [$pomnikiHistorii->id]);

        $this->attachImages($westerplatte, 'westerplatte', 'Westerplatte');

        foreach ([
            [
                'directory' => 'zamek-cesarski-w-poznaniu',
                'attributes' => [
                    'title' => 'Zamek Cesarski',
                    'lead' => 'Monumentalna rezydencja cesarska i jeden z symboli Poznania.',
                    'description' => 'Zamek Cesarski powstał na początku XX wieku jako ostatnia cesarska rezydencja w Europie i dziś pełni funkcje kulturalne.',
                    'locality_id' => $localities['Poznań']->id,
                    'latitude' => 52.4087,
                    'longitude' => 16.9283,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(16.9283000 52.4087000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(2),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(18),
                ],
                'objectTypeIds' => [$zamkiPalace->id],
            ],
            [
                'directory' => 'katedra-poznan',
                'attributes' => [
                    'title' => 'Bazylika Archikatedralna św. Piotra i Pawła',
                    'lead' => 'Najstarsza polska katedra i nekropolia pierwszych władców.',
                    'description' => 'Katedra na Ostrowie Tumskim jest jednym z najważniejszych zabytków sakralnych w Polsce.',
                    'locality_id' => $localities['Poznań']->id,
                    'latitude' => 52.4215,
                    'longitude' => 16.9470,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(16.9470000 52.4215000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(3),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(19),
                ],
                'objectTypeIds' => [$obiektySakralne->id],
            ],
            [
                'directory' => 'muzeum-narodowe-poznan',
                'attributes' => [
                    'title' => 'Muzeum Narodowe',
                    'lead' => 'Jedna z najważniejszych kolekcji sztuki w kraju.',
                    'description' => 'Muzeum Narodowe w Poznaniu prezentuje bogate zbiory malarstwa, rzeźby i rzemiosła artystycznego.',
                    'locality_id' => $localities['Poznań']->id,
                    'latitude' => 52.4063,
                    'longitude' => 16.9242,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(16.9242000 52.4063000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(4),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(20),
                ],
                'objectTypeIds' => [$muzea->id],
            ],
            [
                'directory' => 'zamek-krolewski-warszawa',
                'attributes' => [
                    'title' => 'Zamek Królewski',
                    'lead' => 'Rezydencja królewska i ważny symbol państwowości.',
                    'description' => 'Odbudowany po zniszczeniach wojennych zamek jest jednym z najważniejszych zabytków Warszawy.',
                    'locality_id' => $localities['Warszawa']->id,
                    'latitude' => 52.2477,
                    'longitude' => 21.0137,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(21.0137000 52.2477000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(5),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(21),
                ],
                'objectTypeIds' => [$zamkiPalace->id],
            ],
            [
                'directory' => 'palac-kultury',
                'attributes' => [
                    'title' => 'Pałac Kultury i Nauki',
                    'lead' => 'Ikona powojennej Warszawy i najwyższy zabytkowy wieżowiec w Polsce.',
                    'description' => 'PKiN jest jednym z najbardziej rozpoznawalnych punktów na mapie Warszawy i ważnym centrum wydarzeń.',
                    'locality_id' => $localities['Warszawa']->id,
                    'latitude' => 52.2318,
                    'longitude' => 21.0067,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(21.0067000 52.2318000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(6),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(22),
                ],
                'objectTypeIds' => [$zabytkiTechniki->id],
            ],
            [
                'directory' => 'muzeum-powstania-warszawskiego',
                'attributes' => [
                    'title' => 'Muzeum Powstania Warszawskiego',
                    'lead' => 'Nowoczesne muzeum upamiętniające powstańców Warszawy.',
                    'description' => 'Ekspozycja muzeum opowiada o przebiegu i znaczeniu Powstania Warszawskiego.',
                    'locality_id' => $localities['Warszawa']->id,
                    'latitude' => 52.2328,
                    'longitude' => 20.9818,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(20.9818000 52.2328000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(7),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(23),
                ],
                'objectTypeIds' => [$muzea->id],
            ],
            [
                'directory' => 'pomnik-bohaterow-getta',
                'attributes' => [
                    'title' => 'Pomnik Bohaterów Getta',
                    'lead' => 'Najważniejsze warszawskie miejsce pamięci o powstaniu w getcie.',
                    'description' => 'Pomnik upamiętnia bohaterów powstania w getcie warszawskim i jest jednym z kluczowych miejsc pamięci stolicy.',
                    'locality_id' => $localities['Warszawa']->id,
                    'latitude' => 52.2497,
                    'longitude' => 20.9964,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(20.9964000 52.2497000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(8),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(24),
                ],
                'objectTypeIds' => [$pomnikiHistorii->id],
            ],
            [
                'directory' => 'malbork',
                'attributes' => [
                    'title' => 'Zamek w Malborku',
                    'lead' => 'Największy ceglany zamek na świecie i wpis UNESCO.',
                    'description' => 'Potężny zespół zamkowy Zakonu Krzyżackiego należy do najważniejszych zabytków średniowiecznej Europy.',
                    'locality_id' => $localities['Malbork']->id,
                    'is_unesco' => true,
                    'latitude' => 54.0377,
                    'longitude' => 19.0264,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(19.0264000 54.0377000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(9),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(25),
                ],
                'objectTypeIds' => [$zamkiPalace->id],
            ],
            [
                'directory' => 'wieliczka',
                'attributes' => [
                    'title' => 'Kopalnia Soli w Wieliczce',
                    'lead' => 'Legendarna kopalnia soli i jeden z najbardziej znanych obiektów UNESCO w Polsce.',
                    'description' => 'Podziemny kompleks tras turystycznych i kaplic zachwyca skalą oraz historią górnictwa solnego.',
                    'locality_id' => $localities['Wieliczka']->id,
                    'is_unesco' => true,
                    'latitude' => 49.9831,
                    'longitude' => 20.0600,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(20.0600000 49.9831000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(10),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(26),
                ],
                'objectTypeIds' => [$zabytkiTechniki->id],
            ],
            [
                'directory' => 'stare-miasto-krakow',
                'attributes' => [
                    'title' => 'Stare Miasto w Krakowie',
                    'lead' => 'Historyczne serce Krakowa i jedno z najcenniejszych założeń urbanistycznych w Europie.',
                    'description' => 'Zabytkowe centrum Krakowa łączy średniowieczny układ ulic z najważniejszymi zabytkami miasta.',
                    'locality_id' => $localities['Kraków']->id,
                    'is_unesco' => true,
                    'latitude' => 50.0619,
                    'longitude' => 19.9372,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(19.9372000 50.0619000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(11),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(27),
                ],
                'objectTypeIds' => [$glowneMiasta->id],
            ],
            [
                'directory' => 'bazylika-mariacka-gdansk',
                'attributes' => [
                    'title' => 'Bazylika Mariacka w Gdańsku',
                    'lead' => 'Gotycka świątynia dominująca nad panoramą Gdańska.',
                    'description' => 'Bazylika Mariacka jest jedną z największych ceglastych świątyń na świecie i ważnym zabytkiem sakralnym.',
                    'locality_id' => $localities['Gdańsk']->id,
                    'latitude' => 54.3489,
                    'longitude' => 18.6470,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(18.6470000 54.3489000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(12),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(28),
                ],
                'objectTypeIds' => [$obiektySakralne->id],
            ],
            [
                'directory' => 'skansen-sanok',
                'attributes' => [
                    'title' => 'Skansen w Sanoku',
                    'lead' => 'Największy skansen w Polsce i ważna lekcja kultury Pogórza i Bieszczadów.',
                    'description' => 'Muzeum Budownictwa Ludowego w Sanoku prezentuje architekturę i życie codzienne dawnych mieszkańców regionu.',
                    'locality_id' => $localities['Sanok']->id,
                    'latitude' => 49.5600,
                    'longitude' => 22.2062,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(22.2062000 49.5600000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(13),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(29),
                ],
                'objectTypeIds' => [$skanseny->id],
            ],
            [
                'directory' => 'auschwitz',
                'attributes' => [
                    'title' => 'Muzeum Auschwitz-Birkenau',
                    'lead' => 'Miejsce pamięci i muzeum upamiętniające ofiary obozu Auschwitz.',
                    'description' => 'Były niemiecki nazistowski obóz koncentracyjny i zagłady jest jednym z najważniejszych miejsc pamięci na świecie.',
                    'locality_id' => $localities['Oświęcim']->id,
                    'latitude' => 50.0340,
                    'longitude' => 19.1785,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(19.1785000 50.0340000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(14),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(30),
                ],
                'objectTypeIds' => [$pomnikiHistorii->id],
            ],
            [
                'directory' => 'swieta-lipka',
                'attributes' => [
                    'title' => 'Święta Lipka',
                    'lead' => 'Barokowe sanktuarium pielgrzymkowe na Mazurach.',
                    'description' => 'Kompleks kościelno-klasztorny słynie z architektury, organów i roli w dziejach religijnych regionu.',
                    'locality_id' => $localities['Święta Lipka']->id,
                    'latitude' => 54.0192,
                    'longitude' => 21.2261,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(21.2261000 54.0192000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(15),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(31),
                ],
                'objectTypeIds' => [$obiektySakralne->id],
            ],
            [
                'directory' => 'kopalnia-guido',
                'attributes' => [
                    'title' => 'Kopalnia Guido',
                    'lead' => 'Historyczna kopalnia węgla z jedną z najciekawszych tras podziemnych w Polsce.',
                    'description' => 'Guido pokazuje dziedzictwo przemysłowe Górnego Śląska i umożliwia zwiedzanie pod ziemią.',
                    'locality_id' => $localities['Zabrze']->id,
                    'latitude' => 50.3051,
                    'longitude' => 18.7835,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(18.7835000 50.3051000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(16),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(32),
                ],
                'objectTypeIds' => [$muzea->id],
            ],
            [
                'directory' => 'hala-stulecia',
                'attributes' => [
                    'title' => 'Hala Stulecia',
                    'lead' => 'Ikona modernistycznej architektury i ważny punkt Wrocławia.',
                    'description' => 'Monumentalna hala wpisana na listę UNESCO jest jednym z najważniejszych obiektów architektonicznych miasta.',
                    'locality_id' => $localities['Wrocław']->id,
                    'is_unesco' => true,
                    'latitude' => 51.1067,
                    'longitude' => 17.0770,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(17.0770000 51.1067000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(17),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(33),
                ],
                'objectTypeIds' => [$zabytkiTechniki->id],
            ],
            [
                'directory' => 'bieszczadzki-park-narodowy',
                'attributes' => [
                    'title' => 'Bieszczadzki Park Narodowy',
                    'lead' => 'Dzika część Karpat z połoninami i rozległymi lasami.',
                    'description' => 'Park chroni najbardziej naturalne fragmenty polskich Bieszczadów i jest rajem dla miłośników wędrówek.',
                    'locality_id' => $localities['Ustrzyki Górne']->id,
                    'latitude' => 49.1502,
                    'longitude' => 22.6200,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(22.6200000 49.1502000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(18),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(34),
                ],
                'objectTypeIds' => [$parkiNarodowe->id],
            ],
            [
                'directory' => 'lancut',
                'attributes' => [
                    'title' => 'Zamek w Łańcucie',
                    'lead' => 'Rezydencja magnacka znana z pięknych wnętrz i ogrodów.',
                    'description' => 'Zespół zamkowo-parkowy w Łańcucie należy do najcenniejszych rezydencji arystokratycznych w Polsce.',
                    'locality_id' => $localities['Łańcut']->id,
                    'latitude' => 50.0687,
                    'longitude' => 22.2320,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(22.2320000 50.0687000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(19),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(35),
                ],
                'objectTypeIds' => [$zamkiPalace->id],
            ],
            [
                'directory' => 'kornik',
                'attributes' => [
                    'title' => 'Zamek w Kórniku',
                    'lead' => 'Neogotycka rezydencja nad jeziorem i jeden z symboli Kórnika.',
                    'description' => 'Zamek w Kórniku łączy funkcje rezydencji i muzeum, a jego otoczenie tworzy rozpoznawalny krajobraz Wielkopolski.',
                    'locality_id' => $localities['Kórnik']->id,
                    'latitude' => 52.2400,
                    'longitude' => 17.0899,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(17.0899000 52.2400000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(20),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(36),
                ],
                'objectTypeIds' => [$zamkiPalace->id],
            ],
            [
                'directory' => 'muzeum-wsi-opolskiej',
                'attributes' => [
                    'title' => 'Muzeum Wsi Opolskiej',
                    'lead' => 'Skansen prezentujący architekturę i kulturę regionu opolskiego.',
                    'description' => 'Muzeum na wolnym powietrzu pokazuje zabytkowe budownictwo wiejskie i tradycje dawnego Śląska Opolskiego.',
                    'locality_id' => $localities['Opole']->id,
                    'latitude' => 50.6827,
                    'longitude' => 17.9207,
                    'geometry' => DB::raw("ST_GeomFromText('POINT(17.9207000 50.6827000)', 4326)"),
                    'status' => 'published',
                    'published' => true,
                    'published_at' => now()->subDays(21),
                    'data_source' => 'PTTK',
                    'source_updated_at' => now()->subDays(37),
                ],
                'objectTypeIds' => [$skanseny->id],
            ],
        ] as $definition) {
            $object = $this->createSightseeingObject($definition['attributes'], $definition['objectTypeIds']);

            $this->attachImages($object, $definition['directory'], $definition['attributes']['title']);
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
        if (app()->runningUnitTests()) {
            return;
        }

        $imagesPath = database_path('fixtures/images/'.$directory);

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

            $object->copyMedia($imagePath)
                ->withCustomProperties($mediaProperties)
                ->toMediaCollection('images');
        }
    }

    private function ensureObjectType(string $name): ObjectType
    {
        return ObjectType::query()->updateOrCreate([
            'name' => $name,
        ]);
    }
}
