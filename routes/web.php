<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');

// In order to access any route other than the landing page, the user must be logged in and have a verified email
Route::middleware(['auth','verified'])->group(function () {

    Route::inertia('/dashboard', 'Dashboard')
        ->name('dashboard');

    
    Route::inertia('/pokemon/{pokemon}', 'pokemon/Pokemon')
        ->name('pokemon.show');

    // "manage" is an umbrella permission name to represent full CRUD (Create, Read, Update, Delete) privileges over a specific resource or module
    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['role:admin','verified'])
        ->group(function () {

            Route::inertia('/', 'admin/Dashboard')
                ->name('index');

            Route::prefix('users')->group(function () {
                Route::inertia('/', 'admin/users/Users')
                    ->name('users');

                Route::get('/{user}', function (App\Models\User $user) {
                    return Inertia::render('admin/users/User', [
                        'userId' => $user->id,
                    ]);
                })->name('admin.users.show');

            })->middleware('permission:manage users');

            Route::prefix('pokemon')->group(function () {

                Route::inertia('/manage', 'admin/pokemon/Manage')
                    ->name('pokemon.manage');

                Route::inertia('/import', 'admin/pokemon/Import')
                    ->name('pokemon.import');

                Route::inertia('/batches', 'admin/pokemon/Batches')
                    ->name('pokemon.batches');

                Route::inertia('/{pokemon}', 'admin/pokemon/Pokemon')
                    ->name('pokemon.admin.show');

                Route::get('/batches/{batch}', function ($batch) {
                    return Inertia::render('admin/pokemon/Batch', [
                        'id' => (int) $batch,
                    ]);
                })
                ->name('pokemon.batches.show');
            })->middleware('permission:manage pokemon');
        });
});

require __DIR__.'/settings.php';
