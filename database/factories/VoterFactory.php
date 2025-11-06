<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Ward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voter>
 */
class VoterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
    public function definition(): array
    {
        return [
            'serial_number' => 'VOTER' . str_pad(fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'ward_id' => Ward::factory(),
            'panchayat_id' => Panchayat::factory(),
            'panchayat' => function (array $attributes) {
                return \App\Models\Panchayat::find($attributes['panchayat_id'])->name;
            },
            'image_path' => null,
            'status' => false,
        ];
    }

    /**
     * Indicate that the voter has voted.
     */
    public function voted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => true,
        ]);
    }
}
