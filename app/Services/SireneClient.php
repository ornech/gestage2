<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SireneClient
{
    /** Statut HTTP de la dernière requête (null si l'API n'a pas répondu). */
    public ?int $status = null;

    public function getBySiret(string $siret)
    {
        $this->status = null;

        try {
            $response = Http::timeout(10)->withHeaders([
                'Accept' => 'application/json',
                'X-INSEE-Api-Key-Integration' => config('services.sirene.key'),
            ])->get(config('services.sirene.url') . $siret);
        } catch (\Throwable $e) {
            Log::warning('Sirene : appel impossible', ['siret' => $siret, 'erreur' => $e->getMessage()]);

            return null;
        }

        $this->status = $response->status();

        if ($response->successful()) {
            return $response->json();
        }

        if ($this->status !== 404) {
            Log::warning('Sirene : réponse en erreur', ['siret' => $siret, 'status' => $this->status, 'corps' => mb_substr($response->body(), 0, 300)]);
        }

        return null;
    }
}
