<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStageRequest extends FormRequest
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
        'date_debut' => 'required|date',
        'entreprise_id' => 'required|exists:entreprises,id',
        'maitre_de_stage_id' => 'required|exists:employes,id',
    ];
}


}
