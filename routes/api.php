<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rotas públicas de autenticação
Route::prefix('auth')->group(function () {
    Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login']);
});

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->group(function () {
    
    // Rotas de autenticação protegidas
    Route::prefix('auth')->group(function () {
        Route::post('logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
        Route::post('logout-all', [App\Http\Controllers\Api\AuthController::class, 'logoutAll']);
        Route::get('me', [App\Http\Controllers\Api\AuthController::class, 'me']);
        Route::put('profile', [App\Http\Controllers\Api\AuthController::class, 'updateProfile']);
        Route::get('verify-token', [App\Http\Controllers\Api\AuthController::class, 'verifyToken']);
    });

    // Rota de usuário (legacy - manter compatibilidade)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Entry Guide Routes (protegidas)
    Route::prefix('entry-guide')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\EntryGuideController::class, 'getGuide']);
        Route::post('/entry', [App\Http\Controllers\Api\EntryGuideController::class, 'recordEntry']);
        Route::post('/exit', [App\Http\Controllers\Api\EntryGuideController::class, 'recordExit']);
        Route::get('/valid-guides', [App\Http\Controllers\Api\EntryGuideController::class, 'listValidGuides']);
    });

    // Aqui você pode adicionar outras rotas protegidas da API
    // Exemplo:
    // Route::prefix('work-schedule')->group(function () {
    //     Route::get('/', [App\Http\Controllers\Api\WorkScheduleController::class, 'index']);
    //     Route::post('/', [App\Http\Controllers\Api\WorkScheduleController::class, 'store']);
    //     // etc...
    // });
});

// Rotas públicas para Entry Guide (sem autenticação - para porteiros/seguranças)
Route::prefix('public/entry-guide')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\EntryGuideController::class, 'getGuide']);
    Route::post('/entry', [App\Http\Controllers\Api\EntryGuideController::class, 'recordEntry']);
    Route::post('/exit', [App\Http\Controllers\Api\EntryGuideController::class, 'recordExit']);
});
