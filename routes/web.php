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
        });
});

require __DIR__.'/settings.php';
