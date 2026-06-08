<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        //
    
        \App\Models\Stage::class => \App\Policies\StagePolicy::class,
        \App\Models\Employe::class => \App\Policies\EmployePolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
