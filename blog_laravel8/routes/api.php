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
    // user
    Route::get('/user', [App\Http\Controllers\Api\UserController::class, 'userInfo']);
    Route::post('/edit/user/{user}', [App\Http\Controllers\Api\UserController::class, 'update']);
    Route::post('/delete/user', [App\Http\Controllers\Api\UserController::class, 'update']);
    // post
    Route::get('/post', [App\Http\Controllers\Api\PostController::class, 'index']);
    Route::post('/create/post', [App\Http\Controllers\Api\PostController::class, 'store']);
    Route::get('/edit/post/{post}', [App\Http\Controllers\Api\PostController::class, 'edit']);
    Route::post('/edit/post/{post}', [App\Http\Controllers\Api\PostController::class, 'update']);
    Route::post('/delete/post', [App\Http\Controllers\Api\PostController::class, 'destroy']);
    Route::post('/restore/post', [App\Http\Controllers\Api\PostController::class, 'restore']);
    // category
    Route::post('create/category', [App\Http\Controllers\Api\CategoryController::class, 'store']);
    Route::get('edit/category/{category}', [App\Http\Controllers\Api\CategoryController::class, 'edit']);
    Route::post('edit/category/{category}', [App\Http\Controllers\Api\CategoryController::class, 'update']);
    Route::post('delete/category', [App\Http\Controllers\Api\CategoryController::class, 'destroy']);
    Route::post('restore/category', [App\Http\Controllers\Api\CategoryController::class, 'restore']);
    Route::get('/category', [App\Http\Controllers\Api\CategoryController::class, 'index']);
    // photo
    Route::post('save', [App\Http\Controllers\Api\PhotoController::class, 'store']);
    Route::get('edit/photo/{photo}', [App\Http\Controllers\Api\PhotoController::class, 'edit']);
    Route::post('edit/photo/{photo}', [App\Http\Controllers\Api\PhotoController::class, 'update']);
    Route::delete('delete/photo/{photo}', [App\Http\Controllers\Api\PhotoController::class, 'destroy']);
});

Route::get('get/photo', [App\Http\Controllers\Api\PhotoController::class, 'index']);
Route::get('get/photo/{photo}', [App\Http\Controllers\Api\PhotoController::class, 'show']);
