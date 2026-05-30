<?php

use App\Http\Middleware\SetSecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
// Ajout Spatie (Orthographe exacte des namespaces au singulier)
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
            $middleware->append(\App\Http\Middleware\SetSecurityHeaders::class);
       

        // Enregistrement des alias de Spatie pour la gestion des rôles et permissions
        $middleware->alias([
            'role'                    => RoleMiddleware::class,
            'permission'              => PermissionMiddleware::class,
            'role_or_permission'      => RoleOrPermissionMiddleware::class,
            'cgu'                     => \App\Http\Middleware\EnsureCguAccepted::class,
            'force_password_change'   => \App\Http\Middleware\ForcePasswordChange::class,
            'student_active'          => \App\Http\Middleware\EnsureStudentActive::class,
            'student_has_stage'       => \App\Http\Middleware\EnsureStudentHasStage::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\ForcePasswordChange::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureStudentActive::class);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
