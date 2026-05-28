<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordChangeController extends Controller
{
    public function show()
    {
        return view('auth.first-change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);

        $request->user()->update([
            'password'              => Hash::make($request->password),
            'force_password_change' => false,
        ]);

        return redirect()->intended('/')->with('success', 'Mot de passe modifié avec succès.');
    }
}
