<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    public function create(array $input): User
    {
       

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],

            'password' => ['required', 'string', 'min:8'],

            'idTuteur' => ['nullable', 'integer'],
            'idClasse' => ['nullable', 'integer'],
            'date_entree' => ['nullable', 'date'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'spe' => ['nullable', 'string', 'max:255'],
            'classe' => ['nullable', 'string', 'max:255'],
            'promo' => ['nullable', 'string', 'max:255'],
            'login' => ['nullable', 'string', 'max:255'],
            'reset' => ['nullable', 'integer'],
            'statut' => ['nullable', 'string', 'max:255'],
            'inactif' => ['boolean'],
            'dateFirstConn' => ['nullable', 'date'],
            'deleted' => ['boolean'],
        ])->validate();

        session()->flash('status', 'Compte créé avec succès.');

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),

            'idTuteur' => $input['idTuteur'] ?? null,
            'idClasse' => $input['idClasse'] ?? null,
            'date_entree' => $input['date_entree'] ?? null,
            'telephone' => $input['telephone'] ?? null,
            'spe' => $input['spe'] ?? null,
            'classe' => $input['classe'] ?? null,
            'promo' => $input['promo'] ?? null,
            'login' => $input['login'] ?? null,
            'reset' => $input['reset'] ?? 0,
            'statut' => $input['statut'] ?? null,
            'inactif' => $input['inactif'] ?? 0,
            'dateFirstConn' => $input['dateFirstConn'] ?? null,
            'deleted' => $input['deleted'] ?? 0,
        ]);
    }
}
