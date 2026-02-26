<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],

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
        ])->validateWithBag('updateProfileInformation');

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {

            $this->updateVerifiedUser($user, $input);

        } else {

            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],

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
            ])->save();
        }
    }

    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,

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
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
