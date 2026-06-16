<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\UserController;

/*
|--------------------------------------------------------------------------
| User Pokémon Features
|--------------------------------------------------------------------------
| Authenticated user-only functionality
*/

/**
 * Get user's favorite Pokémon list
 */
Route::get('/favorites', [UserController::class, 'favorites']);

/**
 * Add Pokémon to favorites
 */
Route::post('/favorites/{pokemon}', [UserController::class, 'favorite']);

/**
 * Remove Pokémon from favorites
 */
Route::delete('/favorites/{pokemon}', [UserController::class, 'removeFavorite']);