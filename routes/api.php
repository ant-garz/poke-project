<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
*/
Route::prefix('v1/public')->group(function () {
    require __DIR__ . '/api/public.php';
});

/*
|--------------------------------------------------------------------------
| User API (authenticated users)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/user')
    ->middleware(['web','auth:web','role:user','verified'])
    ->group(function () {
        require __DIR__ . '/api/user.php';
    });

/*
|--------------------------------------------------------------------------
| Admin API
|--------------------------------------------------------------------------
*/
Route::prefix('v1/admin')
    ->middleware([ 'web','auth:web', 'role:admin','verified'])
    ->group(function () {
        require __DIR__ . '/api/admin.php';
    });