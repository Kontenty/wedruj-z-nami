<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $localityVoivodeshipMap = [
            'Kraków' => 'małopolskie',
            'Kozłówka' => 'lubelskie',
            'Białowieża' => 'podlaskie',
            'Osowiec-Twierdza' => 'podlaskie',
            'Częstochowa' => 'śląskie',
            'Gdańsk' => 'pomorskie',
            'Poznań' => 'wielkopolskie',
            'Warszawa' => 'mazowieckie',
            'Malbork' => 'pomorskie',
            'Wieliczka' => 'małopolskie',
            'Sanok' => 'podkarpackie',
            'Oświęcim' => 'małopolskie',
            'Święta Lipka' => 'warmińsko-mazurskie',
            'Zabrze' => 'śląskie',
            'Wrocław' => 'dolnośląskie',
            'Ustrzyki Górne' => 'podkarpackie',
            'Łańcut' => 'podkarpackie',
            'Kórnik' => 'wielkopolskie',
            'Opole' => 'opolskie',
        ];

        foreach ($localityVoivodeshipMap as $localityName => $voivodeshipName) {
            $voivodeship = DB::table('voivodeships')
                ->where('name', $voivodeshipName)
                ->first();

            if (! $voivodeship) {
                continue;
            }

            $slug = str($localityName)->slug()->value();

            $existing = DB::table('localities')
                ->where('slug', $slug)
                ->first();

            if ($existing) {
                $localityId = $existing->id;
            } else {
                $localityId = DB::table('localities')->insertGetId([
                    'name' => $localityName,
                    'slug' => $slug,
                    'description' => null,
                    'voivodeship_id' => $voivodeship->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('sightseeing_objects')
                ->whereRaw('LOWER(locality) = ?', [mb_strtolower($localityName)])
                ->whereNull('locality_id')
                ->update(['locality_id' => $localityId]);
        }
    }

    public function down(): void
    {
        DB::table('sightseeing_objects')->update(['locality_id' => null]);
        DB::table('localities')->truncate();
    }
};
