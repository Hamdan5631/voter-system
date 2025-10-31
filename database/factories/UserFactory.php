<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake()->phoneNumber(),
            'role' => 'worker',
            'ward_id' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is a superadmin.
     */
    public function superadmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'superadmin',
            'ward_id' => null,
        ]);
    }

    /**
     * Indicate that the user is a team lead.
     */
    public function teamLead(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'team_lead',
        ]);
    }

    /**
     * Indicate that the user is a booth agent.
     */
    public function boothAgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'booth_agent',
        ]);
    }

    /**
     * Indicate that the user is a worker.
     */
    public function worker(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'worker',
        ]);
    }
}
