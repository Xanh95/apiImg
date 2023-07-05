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

Route::post('/register', [App\Http\Controllers\ApiUserController::class, 'register']);
Route::post('/login', [App\Http\Controllers\ApiUserController::class, 'login']);
Route::get('/user', [App\Http\Controllers\ApiUserController::class, 'userInfo'])->middleware('auth:api');
Route::post('/create', [App\Http\Controllers\ApiPostController::class, 'store'])->middleware('auth:api');
Route::get('/post', [App\Http\Controllers\ApiPostController::class, 'index'])->middleware('auth:api');
Route::put('/edit/{post}', [App\Http\Controllers\ApiPostController::class, 'update'])->middleware('auth:api');
Route::delete('/delete/{post}', [App\Http\Controllers\ApiPostController::class, 'destroy'])->middleware('auth:api');
Route::get('/post/{post}', [App\Http\Controllers\ApiPostController::class, 'show']);
Route::post('save', [App\Http\Controllers\ApiImg::class, 'store']);
Route::post('edit/photo/{photo}', [App\Http\Controllers\ApiImg::class, 'update']);
Route::get('edit/photo/{photo}', [App\Http\Controllers\ApiImg::class, 'edit']);
Route::get('get/photo', [App\Http\Controllers\ApiImg::class, 'index']);
Route::delete('delete/photo/{photo}', [App\Http\Controllers\ApiImg::class, 'destroy']);
Route::get('get/photo/{photo}', [App\Http\Controllers\ApiImg::class, 'show']);
