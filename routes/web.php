<?php

use Illuminate\Support\Facades\Route;




Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])
    ->group(function () {

        // User area
        Route::inertia('/dashboard', 'Dashboard')
            ->name('dashboard');

        // Admin area
        Route::prefix('admin')
        ->name('admin.')
        ->middleware(['role:admin'])
        ->group(function () {

            Route::inertia('/dashboard', 'admin/Dashboard')
                ->name('dashboard');

            Route::inertia('/users', 'admin/Users')
                ->middleware('permission:view users')
                ->name('users');

            Route::inertia('/roles', 'admin/Roles')
                ->middleware('permission:manage roles')
                ->name('roles');

            // pokemon admin routes
            Route::inertia('/pokemon', 'admin/pokemon/Index')
                ->middleware('permission:manage pokemon')
                ->name('admin.pokemon');

            Route::inertia('/pokemon/import', 'admin/pokemon/Import')
                ->middleware('permission:manage pokemon')
                ->name('admin.pokemon.import');

            Route::inertia('/pokemon/manage', 'admin/pokemon/Manage')
                ->middleware('permission:manage pokemon')
                ->name('admin.pokemon.manage');
        });
});

require __DIR__.'/settings.php';
