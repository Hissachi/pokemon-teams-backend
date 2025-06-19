<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rotas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/characters', [AuthController::class, 'getApi']);

Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    // Revoga todos os tokens do usuário
    $request->user()->tokens()->delete();
    
    return response()->json([
        'message' => 'Logout realizado com sucesso'
    ]);
});
// Rotas protegidas (requerem autenticação)
Route::middleware('auth:sanctum')->group(function () {
    // Rotas de usuário
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    // Revoga todos os tokens do usuário
    $request->user()->tokens()->delete();
    
    return response()->json([
        'message' => 'Logout realizado com sucesso'
    ]);
});
    
    // Rotas de times
    Route::prefix('teams')->group(function () {
        Route::post('/', [AuthController::class, 'createTeam']);
        Route::get('/', [AuthController::class, 'listTeams']);
        Route::delete('/{team}', [AuthController::class, 'deleteTeam']);
        Route::post('/{team}/pokemons', [AuthController::class, 'addPokemon']);
        Route::delete('/{team}/pokemons/{pokemon}', [AuthController::class, 'deletePokemon']);
    });
});

Route::get('/hello', function (Request $request) {
    return response()->json([
        'code' => 200,
        'message' => 'Hello, World!'
    ]);
});