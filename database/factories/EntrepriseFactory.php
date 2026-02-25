<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class EntrepriseFactory extends Factory
{
    public function definition(): array
    {
        // Utilisation de Faker pour générer du faux contenu réaliste
        return [
            'raison_sociale' => $this->faker->company(),
            'siret' => $this->faker->numerify('##############'), // 14 chiffres
            'code_naf' => $this->faker->regexify('[0-9]{4}[A-Z]'), // Ex: 6201Z
            'adresse' => $this->faker->streetAddress(),
            'complement_adresse' => $this->faker->optional()->secondaryAddress(),
            'code_postal' => $this->faker->numerify('#####'), // Force 5 chiffres
            'ville' => $this->faker->city(),
            'departement_code' => $this->faker->numerify('##'),
            'telephone' => $this->faker->phoneNumber(),
            'type' => $this->faker->randomElement(['SA', 'SARL', 'SAS', 'EURL']),
            'effectif' => $this->faker->numberBetween(1, 500),
            'est_valide' => $this->faker->boolean(80), // 80% de chance d'être validé

            // Créera automatiquement un utilisateur si aucun n'est fourni
            'user_id' => User::factory(),
        ];
    }
}
