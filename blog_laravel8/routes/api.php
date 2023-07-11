<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [App\Http\Controllers\Api\UserController::class, 'register']);
Route::post('/login', [App\Http\Controllers\Api\UserController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [App\Http\Controllers\Api\UserController::class, 'userInfo']);
    Route::post('/create', [App\Http\Controllers\Api\PostController::class, 'store']);
    Route::get('/post', [App\Http\Controllers\Api\PostController::class, 'index']);
    Route::put('/edit/{post}', [App\Http\Controllers\Api\PostController::class, 'update']);
    Route::delete('/delete/{post}', [App\Http\Controllers\Api\PostController::class, 'destroy']);
    Route::post('save/category', [App\Http\Controllers\Api\CategoryController::class, 'store']);
    Route::get('edit/category/{category}', [App\Http\Controllers\Api\CategoryController::class, 'edit']);
    Route::post('edit/category/{category}', [App\Http\Controllers\Api\CategoryController::class, 'update']);
    Route::delete('delete/category/{category}', [App\Http\Controllers\Api\CategoryController::class, 'destroy']);
    Route::get('/category/{category}', [App\Http\Controllers\Api\CategoryController::class, 'show']);
    Route::get('/category', [App\Http\Controllers\Api\CategoryController::class, 'index']);
});

Route::get('/post/{post}', [App\Http\Controllers\Api\PostController::class, 'show']);
Route::post('save', [App\Http\Controllers\Api\PhotoController::class, 'store']);
Route::post('edit/photo/{photo}', [App\Http\Controllers\Api\PhotoController::class, 'update']);
Route::get('edit/photo/{photo}', [App\Http\Controllers\Api\PhotoController::class, 'edit']);
Route::get('get/photo', [App\Http\Controllers\Api\PhotoController::class, 'index']);
Route::delete('delete/photo/{photo}', [App\Http\Controllers\Api\PhotoController::class, 'destroy']);
Route::get('get/photo/{photo}', [App\Http\Controllers\Api\PhotoController::class, 'show']);