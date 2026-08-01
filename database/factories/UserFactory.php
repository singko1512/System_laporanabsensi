<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'pembimbing_magang' => fake()->name(),
            'bidang_magang' => fake()->randomElement(['Backend Developer', 'Frontend Developer', 'Quality Assurance', 'UI/UX Designer']),
            'tanggal_mulai_magang' => now()->subMonths(3)->toDateString(),
            'tanggal_selesai_magang' => now()->addMonths(3)->toDateString(),
            'role' => 'user',
            'status_akun' => 'aktif',
        ];
    }
}
