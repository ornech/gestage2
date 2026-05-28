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
    $start = fake()->dateTimeBetween('-1 month', '+1 month');
    return [
        'titre' => fake()->sentence(),
        'date_debut' => $start,
        'date_fin' => fake()->dateTimeBetween($start, '+2 months'),
        'entreprise_id' => \App\Models\Entreprise::factory(),
        'maitre_de_stage_id' => \App\Models\Employe::factory(),
    ];
}

}
