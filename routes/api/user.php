<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\FavoritePokemonController;

/*
|--------------------------------------------------------------------------
| User Pokémon Features
|--------------------------------------------------------------------------
| Authenticated user-only functionality
*/

/**
 * Get user's favorite Pokémon list
 */
Route::get('/favorites', [FavoritePokemonController::class, 'index']);

/**
 * Add Pokémon to favorites
 */
Route::post('/favorites/{pokemon}', [FavoritePokemonController::class, 'store']);

/**
 * Remove Pokémon from favorites
 */
Route::delete('/favorites/{pokemon}', [FavoritePokemonController::class, 'destroy']);