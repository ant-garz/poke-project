<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PokemonController;

/*
|--------------------------------------------------------------------------
| Public Pokémon API
|--------------------------------------------------------------------------
| No auth required (or optionally add throttle middleware later)
*/

Route::get('/pokemon', [PokemonController::class, 'index']);

Route::get('/pokemon/search', [PokemonController::class, 'search']);

Route::get('/pokemon/{pokemon}', [PokemonController::class, 'show']);