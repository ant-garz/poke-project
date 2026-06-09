<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\PokemonImportController;
use App\Http\Controllers\Api\Admin\PokemonManagementController;

/*
|--------------------------------------------------------------------------
| Admin Pokémon API
|--------------------------------------------------------------------------
| Requires: auth:sanctum + role:admin
*/

Route::post('/pokemon/import', [PokemonImportController::class, 'store']);

Route::post('/pokemon/reprocess', [PokemonManagementController::class, 'reprocess']);

Route::patch('/pokemon/{pokemon}', [PokemonManagementController::class, 'update']);

Route::delete('/pokemon/{pokemon}', [PokemonManagementController::class, 'destroy']);