<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\PokemonImportController;
use App\Http\Controllers\Api\Admin\PokemonManagementController;
use App\Http\Controllers\Api\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Admin Pokémon API
|--------------------------------------------------------------------------
| Requires: auth:sanctum + role:admin
*/

Route::post('/pokemon/import', [PokemonImportController::class, 'store']);

/**
 * imports
 */
// list all imports (history page)
Route::get('/pokemon/import/batches', [PokemonImportController::class, 'index']);
// live polling
Route::get('/pokemon/import/{batch}', [PokemonImportController::class, 'show']);
// full detail page
Route::get('/pokemon/import/batches/{batch}', [PokemonImportController::class, 'detail']);

Route::prefix('pokemon')->group(function () {

    Route::post('/{pokemon}/sync/pokeapi', [PokemonManagementController::class, 'syncPokeApi']);
    Route::post('/{pokemon}/sync/tcgdex', [PokemonManagementController::class, 'syncTcgdex']);
    Route::post('/{pokemon}/sync/all', [PokemonManagementController::class, 'syncAll']);
    Route::get('/{pokemon}/sync-status', [PokemonManagementController::class, 'syncStatus']);

    Route::patch('/{pokemon}', [PokemonManagementController::class, 'update']);
    Route::delete('/{pokemon}', [PokemonManagementController::class, 'destroy']);
});

Route::prefix('users')->group(function () {

    Route::get('/', [UserController::class, 'index']);
    Route::get('/{user}', [UserController::class, 'show']);

    Route::delete('/{user}', [UserController::class, 'destroy']);
    Route::post('/{user}/restore', [UserController::class, 'restore']);

    Route::put('/{user}/roles', [UserController::class, 'updateRoles']);
});