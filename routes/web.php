<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');

// In order to access any route other than the landing page, the user must be logged in and have a verified email
Route::middleware(['auth',])->group(function () {

    Route::inertia('/dashboard', 'Dashboard')
        ->name('dashboard');

    // "manage" is an umbrella permission name to represent full CRUD (Create, Read, Update, Delete) privileges over a specific resource or module
    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['role:admin','verified'])
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

                Route::inertia('/batches', 'admin/pokemon/Batches')
                    ->middleware('permission:manage pokemon')
                    ->name('pokemon.batches');

                Route::get('/batches/{batch}', function ($batch) {
                    return Inertia::render('admin/pokemon/Batch', [
                        'id' => (int) $batch,
                    ]);
                })
                ->middleware('permission:manage pokemon')
                ->name('pokemon.batches.show');

                Route::inertia('/manage', 'admin/pokemon/Manage')
                    ->middleware('permission:manage pokemon')
                    ->name('pokemon.manage');
            });
        });
});

require __DIR__.'/settings.php';
