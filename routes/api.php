<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    require __DIR__ . '/api/public.php';
});

/*
|--------------------------------------------------------------------------
| User API (authenticated users)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/user')
    ->middleware(['auth'])
    ->group(function () {
        require __DIR__ . '/api/user.php';
    });

/*
|--------------------------------------------------------------------------
| Admin API
|--------------------------------------------------------------------------
*/
Route::prefix('v1/admin')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        require __DIR__ . '/api/admin.php';
    });