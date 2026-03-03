<?php

namespace App\Http\Controllers;

use App\Models\User;

class TestController extends Controller
{
    public function test()
    {
        return User::first();
    }
}
