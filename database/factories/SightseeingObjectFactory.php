<?php

namespace Database\Factories;

use App\Models\Locality;
use App\Models\SightseeingObject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<SightseeingObject>
 */
class SightseeingObjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $latitude = fake()->randomFloat(7, 49.0020, 54.8358);
        $longitude = fake()->randomFloat(7, 14.1229, 24.1458);

        return [
            'title' => fake()->unique()->sentence(3),
            'lead' => fake()->sentence(12),
            'description' => fake()->paragraphs(3, true),
            'locality_id' => Locality::factory(),
            'is_unesco' => false,
            'opening_hours' => fake()->optional()->sentence(),
            'ticket_prices' => fake()->optional()->sentence(),
            'accessibility' => fake()->optional()->sentence(),
            'website' => fake()->optional()->url(),
            'data_source' => 'PTTK',
            'source_updated_at' => fake()->dateTimeBetween('-1 year'),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'geometry' => self::pointExpression($latitude, $longitude),
            'author_id' => User::factory()->editor(),
            'status' => 'draft',
            'published' => false,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published' => true,
            'published_at' => now()->subDays(fake()->numberBetween(1, 60)),
        ]);
    }

    public function unesco(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_unesco' => true,
        ]);
    }

    public function point(float $latitude, float $longitude): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'geometry' => self::pointExpression($latitude, $longitude),
        ]);
    }

    public function polygon(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => 50.0647,
            'longitude' => 19.9450,
            'geometry' => DB::raw("ST_GeomFromText('POLYGON((19.9300000 50.0500000,19.9600000 50.0500000,19.9600000 50.0800000,19.9300000 50.0800000,19.9300000 50.0500000))', 4326)"),
        ]);
    }

    private static function pointExpression(float $latitude, float $longitude): Expression
    {
        return DB::raw(sprintf("ST_GeomFromText('POINT(%F %F)', 4326)", $longitude, $latitude));
    }
}
