<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stage>
 */
class StageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    return [
        'titre' => fake()->sentence(),
        'date_debut' => fake()->date(),
        'date_fin' => fake()->date(),
        'entreprise_id' => \App\Models\Entreprise::factory(),
        'maitre_de_stage_id' => \App\Models\Employe::factory(),
    ];
}

}
