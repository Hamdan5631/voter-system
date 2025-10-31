<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ward>
 */
class WardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true) . ' Ward',
            'ward_number' => 'WARD' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'panchayat' => fake()->city(),
            'description' => fake()->sentence(),
        ];
    }
}
