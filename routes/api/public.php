<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PokemonController;
use App\Http\Controllers\Api\TypeController;

/*
|--------------------------------------------------------------------------
| Public Pokémon API
|--------------------------------------------------------------------------
| No auth required (or optionally add throttle middleware later)
*/

Route::prefix('pokemon')->group(function () {

    Route::get('/', [PokemonController::class, 'index']);

    Route::get('/search', [PokemonController::class, 'search']);

    Route::get('/{pokemon}', [PokemonController::class, 'show']);

    Route::get('/types', [TypeController::class, 'index']);
});