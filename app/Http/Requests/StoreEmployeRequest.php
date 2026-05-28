<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
public function authorize(): bool
{
    return true;
}

public function rules(): array
{
    return [
        'nom' => 'required|string|max:255',
        'prenom' => 'required|string|max:255',
        'email' => 'required|email|unique:employes,email',
        'entreprise_id' => 'required|exists:entreprises,id',
    ];
}

}
