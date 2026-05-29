<?php

namespace Database\Factories;

use App\Models\Voivodeship;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Voivodeship>
 */
class VoivodeshipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
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
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name, '-', 'pl'),
        ];
    }
}
