<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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
            'nom' => fake()->lastName(),    // Utilise 'nom' au lieu de 'name'
            'prenom' => fake()->firstName(),   // Utilise 'prenom'
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'tuteur_id' => null,
            'classe_id' => null,
            'cgu_accepted_at' => now(),
            'force_password_change' => false,
        ];
    }

    public function etudiant(): static
    {
        return $this->afterCreating(fn ($user) => $user->assignRole(Role::findOrCreate('Etudiant')));
    }

    public function professeur(): static
    {
        return $this->afterCreating(fn ($user) => $user->assignRole(Role::findOrCreate('Professeur')));
    }

    public function administrateur(): static
    {
        return $this->afterCreating(fn ($user) => $user->assignRole(Role::findOrCreate('Administrateur')));
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
