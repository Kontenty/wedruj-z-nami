<?php

namespace Database\Factories;

use App\Models\ObjectType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ObjectType>
 */
class ObjectTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function childOf(ObjectType $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }
}
