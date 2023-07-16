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
    // get user
    Route::get('/user', [App\Http\Controllers\Api\UserController::class, 'userInfo']);
    // post
    Route::get('/get/postInCategory/{category}', [App\Http\Controllers\Api\PostController::class, 'postInCategory']);
    Route::post('/create/post', [App\Http\Controllers\Api\PostController::class, 'store']);
    Route::get('/edit/post/{post}', [App\Http\Controllers\Api\PostController::class, 'edit']);
    Route::post('/edit/post/{post}', [App\Http\Controllers\Api\PostController::class, 'update']);
    Route::delete('/delete/post/{post}', [App\Http\Controllers\Api\PostController::class, 'destroy']);
    // category
    Route::post('create/category', [App\Http\Controllers\Api\CategoryController::class, 'store']);
    Route::get('edit/category/{category}', [App\Http\Controllers\Api\CategoryController::class, 'edit']);
    Route::post('edit/category/{category}', [App\Http\Controllers\Api\CategoryController::class, 'update']);
    Route::delete('delete/category/{category}', [App\Http\Controllers\Api\CategoryController::class, 'destroy']);
    Route::get('/category', [App\Http\Controllers\Api\CategoryController::class, 'index']);
    // photo
    Route::post('save', [App\Http\Controllers\Api\PhotoController::class, 'store']);
    Route::get('edit/photo/{photo}', [App\Http\Controllers\Api\PhotoController::class, 'edit']);
    Route::post('edit/photo/{photo}', [App\Http\Controllers\Api\PhotoController::class, 'update']);
    Route::delete('delete/photo/{photo}', [App\Http\Controllers\Api\PhotoController::class, 'destroy']);
});

Route::get('get/photo', [App\Http\Controllers\Api\PhotoController::class, 'index']);
Route::get('get/photo/{photo}', [App\Http\Controllers\Api\PhotoController::class, 'show']);
