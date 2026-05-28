<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminAuditController extends Controller
{
    public function index(Request $request)
    {
        // TODO : brancher sur un système de logs (ex. spatie/laravel-activitylog)
        $logs = collect();

        return view('admin.audit.index', compact('logs'));
    }
}
