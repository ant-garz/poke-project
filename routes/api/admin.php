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

// manual controls (existing)
Route::post('/pokemon/reprocess', [PokemonManagementController::class, 'reprocess']);
Route::post('/pokemon/sync/{pokemon}', [PokemonManagementController::class, 'sync']);

Route::patch('/pokemon/{pokemon}', [PokemonManagementController::class, 'update']);
Route::delete('/pokemon/{pokemon}', [PokemonManagementController::class, 'destroy']);

Route::prefix('users')->group(function () {

    Route::get('/', [UserController::class, 'index']);
    Route::get('/{user}', [UserController::class, 'show']);

    Route::delete('/{user}', [UserController::class, 'destroy']);
    Route::post('/{user}/restore', [UserController::class, 'restore']);

    Route::put('/{user}/roles', [UserController::class, 'updateRoles']);
});