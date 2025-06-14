<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rotas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/characters', [AuthController::class, 'getApi']);

// Rotas protegidas (requerem autenticação)
Route::middleware('auth:sanctum')->group(function () {
    // Rotas de usuário
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Rotas de times
    Route::prefix('teams')->group(function () {
        Route::post('/', [AuthController::class, 'createTeams']);
        Route::get('/', [AuthController::class, 'listTeams']);
        Route::delete('/{id}', [AuthController::class, 'deleteTeams']);
    });
});