<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('register',[AuthController::class,'register']);

Route::post('/login',[AuthController::class,'login']);

Route::get('/characters',[AuthController::class,'getApi']);


//RESTRINGIR 5 PERSONAGENS POR TIME 

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/teams', [AuthController::class, 'createTeams']);
    Route::get('/teams', [AuthController::class, 'listTeams']);
    Route::delete('/teams/{id}', [AuthController::class, 'deletarTeams']);
});

