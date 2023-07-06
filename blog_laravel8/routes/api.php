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
Route::get('/user', [App\Http\Controllers\Api\UserController::class, 'userInfo'])->middleware('auth:api');
Route::post('/create', [App\Http\Controllers\Api\PostController::class, 'store'])->middleware('auth:api');
Route::get('/post', [App\Http\Controllers\Api\PostController::class, 'index'])->middleware('auth:api');
Route::put('/edit/{post}', [App\Http\Controllers\Api\PostController::class, 'update'])->middleware('auth:api');
Route::delete('/delete/{post}', [App\Http\Controllers\Api\PostController::class, 'destroy'])->middleware('auth:api');
Route::get('/post/{post}', [App\Http\Controllers\Api\PostController::class, 'show']);
Route::post('save', [App\Http\Controllers\Api\ImgController::class, 'store']);
Route::post('edit/photo/{photo}', [App\Http\Controllers\Api\ImgController::class, 'update']);
Route::get('edit/photo/{photo}', [App\Http\Controllers\Api\ImgController::class, 'edit']);
Route::get('get/photo', [App\Http\Controllers\Api\ImgController::class, 'index']);
Route::delete('delete/photo/{photo}', [App\Http\Controllers\Api\ImgController::class, 'destroy']);
Route::get('get/photo/{photo}', [App\Http\Controllers\Api\ImgController::class, 'show']);