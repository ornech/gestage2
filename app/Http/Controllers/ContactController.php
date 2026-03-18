<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Entreprise;
class ContactController extends Controller
{
    public function index(Entreprise $company)
    {
        // Récupérer les contacts liés à l'entreprise
        $contacts = $company->contacts()->paginate(10);

        // Retourner la vue (on la créera plus tard)
        return view('contacts.index', compact('company', 'contacts'));
    }


    public function store(Request $request, Entreprise $company)
    {
        //
    }

    public function show(Entreprise $company, Contact $contact)
    {
        //
    }

    public function update(Request $request, Entreprise $company, Contact $contact)
    {
        //
    }

    public function destroy(Entreprise $company, Contact $contact)
    {
        //
    }
}
