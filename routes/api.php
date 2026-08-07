<?php

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
    Route::get('books/isbn/{isbn}', [BookController::class, 'isbn']);
    Route::apiResource('books', BookController::class);
    Route::apiResource('genres', GenreController::class);
    Route::apiResource('reviews', ReviewController::class)->only(['update', 'destroy']);
    Route::post('books/{book}/reviews', [ReviewController::class, 'store']);
    Route::get('ranking', [RankingController::class, 'index']);
});
