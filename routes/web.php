<?php

use Illuminate\Support\Facades\Route;



Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::inertia('/dashboard', 'Dashboard')
        ->name('dashboard');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['role:admin'])
        ->group(function () {

            Route::inertia('/', 'admin/Dashboard')
                ->name('index');

            Route::inertia('/users', 'admin/Users')
                ->middleware('permission:view users')
                ->name('users');

            Route::inertia('/roles', 'admin/Roles')
                ->middleware('permission:manage roles')
                ->name('roles');

            Route::prefix('pokemon')->group(function () {

                Route::inertia('/', 'admin/pokemon/Index')
                    ->middleware('permission:manage pokemon')
                    ->name('pokemon.index');

                Route::inertia('/import', 'admin/pokemon/Import')
                    ->middleware('permission:manage pokemon')
                    ->name('pokemon.import');

                Route::inertia('/manage', 'admin/pokemon/Manage')
                    ->middleware('permission:manage pokemon')
                    ->name('pokemon.manage');
            });
        });
});

require __DIR__.'/settings.php';
