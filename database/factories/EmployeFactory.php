<?php

namespace Database\Factories;
use App\Models\Entreprise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employe>
 */
class EmployeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   public function definition(): array
{
    return [
      'entreprise_id' => Entreprise::factory(),
            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'email' => $this->faker->unique()->safeEmail(),
            'telephone' => $this->faker->phoneNumber(), // 🔥 OBLIGATOIRE
            'service' => $this->faker->word(),
            'fonction' => $this->faker->jobTitle(),
            'contact_valide' => false,
            'newsletter' => false,
            'jury' => false,
    ];
}

}
