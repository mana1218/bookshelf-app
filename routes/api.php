<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\GenreController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\RankingController;
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

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::get('books/isbn/{isbn}', [BookController::class, 'isbn']);
    Route::get('books', [BookController::class, 'index']);
    Route::get('books/{book}', [BookController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('books', [BookController::class, 'store']);
        Route::put('books/{book}', [BookController::class, 'update']);
        Route::delete('books/{book}', [BookController::class, 'destroy']);
    });
    
    Route::apiResource('genres', GenreController::class);
    Route::apiResource('reviews', ReviewController::class)->only(['update', 'destroy']);
    Route::post('books/{book}/reviews', [ReviewController::class, 'store']);
    Route::get('ranking', [RankingController::class, 'index']);
});
