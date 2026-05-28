<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CguController extends Controller
{
    public function show()
    {
        return view('cgu.show');
    }

    public function accept(Request $request)
    {
        $request->user()->update(['cgu_accepted_at' => now()]);

        return redirect()->intended('/');
    }
}
