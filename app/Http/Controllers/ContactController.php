<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Entreprise;
class ContactController extends Controller
{
    public function index(Entreprise $company)
    {
        //
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
