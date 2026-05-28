<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SireneClient
{
    public function getBySiret(string $siret)
    {
        //on utilise le client HTTP de Laravel pour faire une requête GET à l'API INSEE
         $response = Http::withHeaders([
            'Accept' => 'application/json',
            'X-INSEE-Api-Key-Integration' => config('services.sirene.key'),
        ])->get("https://api.insee.fr/api-sirene/3.11/siret/{$siret}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    
    }
}
