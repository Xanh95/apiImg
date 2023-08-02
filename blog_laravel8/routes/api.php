<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerifyEmailController;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Article;
use App\Models\ReversionArticle;
use App\Models\Toppage;

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


// authencate
Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);


// verify email with pin
Route::post('/verifyPin', [App\Http\Controllers\VerifyEmailController::class, 'verifyPin'])->middleware('auth:api');
// resend link to verify email with pin
Route::post('/resendPin', [App\Http\Controllers\VerifyEmailController::class, 'resendPin'])->middleware('auth:api');

Route::middleware('auth:api', 'verified')->group(function () {
    // user
    Route::get('/user/all', [App\Http\Controllers\Api\UserController::class, 'index'])->can('viewAny', User::class);
    Route::get('/user', [App\Http\Controllers\Api\UserController::class, 'userInfo']);
    Route::post('/create/user', [App\Http\Controllers\Api\UserController::class, 'create'])->can('create', User::class);
    Route::post('/edit/user/{user}', [App\Http\Controllers\Api\UserController::class, 'update'])->can('update', 'user');
    Route::get('/edit/user/{user}', [App\Http\Controllers\Api\UserController::class, 'edit'])->can('update', 'user');
    Route::post('/delete/user', [App\Http\Controllers\Api\UserController::class, 'destroy'])->can('delete', 'user');
    Route::post('/restore/user', [App\Http\Controllers\Api\UserController::class, 'restore'])->can('delete', 'user');
    // update status 
    Route::post('/article/approve/{article}', [App\Http\Controllers\Api\UserController::class, 'approve'])->can('approve', User::class);
    Route::post('/reversion/approve/{reversion}', [App\Http\Controllers\Api\UserController::class, 'approveReversion'])->can('approve', User::class);
    // user favorite
    Route::post('/user/favorite', [App\Http\Controllers\Api\UserController::class, 'addFavorite']);
    Route::get('/user/show/favorite', [App\Http\Controllers\Api\UserController::class, 'showFavorite'])->can('view', User::class);
    // edit my account
    Route::get('/edit/my_account', [App\Http\Controllers\Api\UserController::class, 'password_confirmation'])->can('view', User::class);

    // post
    Route::get('/post', [App\Http\Controllers\Api\PostController::class, 'index'])->can('view', Post::class);
    Route::post('/create/post', [App\Http\Controllers\Api\PostController::class, 'store'])->can('create', Post::class);
    Route::get('/edit/post/{post}', [App\Http\Controllers\Api\PostController::class, 'edit'])->can('view', Post::class);
    Route::post('/edit/post/{post}', [App\Http\Controllers\Api\PostController::class, 'update'])->can('update', 'post');
    Route::post('/edit/post-detail/{post}', [App\Http\Controllers\Api\PostController::class, 'updateDetails'])->can('update', 'post');
    Route::post('/delete/post', [App\Http\Controllers\Api\PostController::class, 'destroy'])->can('delete', Post::class);
    Route::post('/restore/post', [App\Http\Controllers\Api\PostController::class, 'restore'])->can('delete', Post::class);

    // article
    Route::get('/article', [App\Http\Controllers\Api\ArticleController::class, 'index'])->can('view', Article::class);
    Route::post('/create/article', [App\Http\Controllers\Api\ArticleController::class, 'store'])->can('create', Article::class);
    Route::get('/edit/article/{article}', [App\Http\Controllers\Api\ArticleController::class, 'edit'])->can('view', Article::class);
    Route::post('/edit/article/{article}', [App\Http\Controllers\Api\ArticleController::class, 'update'])->can('update', 'article');
    Route::post('/edit/article-detail/{article}', [App\Http\Controllers\Api\ArticleController::class, 'updateDetails'])->can('update', 'article');
    Route::post('/delete/article', [App\Http\Controllers\Api\ArticleController::class, 'destroy'])->can('delete', Article::class);
    Route::post('/restore/article', [App\Http\Controllers\Api\ArticleController::class, 'restore'])->can('delete', Article::class);

    // reversion article
    Route::get('/reversion/article', [App\Http\Controllers\Api\ReversionArticleController::class, 'index'])->can('update', ReversionArticle::class);
    Route::post('/create/reversion/article/{article}', [App\Http\Controllers\Api\ReversionArticleController::class, 'store'])->can('create', ReversionArticle::class);
    Route::get('edit/reversion/article/{reversion}', [App\Http\Controllers\Api\ReversionArticleController::class, 'edit'])->can('update', 'reversion');
    Route::post('edit/reversion/article/{reversion}', [App\Http\Controllers\Api\ReversionArticleController::class, 'update'])->can('update', 'reversion');
    Route::post('/delete/reversion', [App\Http\Controllers\Api\ReversionArticleController::class, 'destroy'])->can('create', ReversionArticle::class);
    Route::post('/restore/reversion', [App\Http\Controllers\Api\ReversionArticleController::class, 'restore'])->can('delete', ReversionArticle::class);
    Route::post('/edit/reversion-detail/{reversion}', [App\Http\Controllers\Api\ReversionArticleController::class, 'updateDetails'])->can('update', 'reversion');
    Route::post('/pending/reversion/article/{reversion}', [App\Http\Controllers\Api\ReversionArticleController::class, 'pending'])->can('update', 'reversion');


    // category
    Route::post('create/category', [App\Http\Controllers\Api\CategoryController::class, 'store'])->can('create', Category::class);
    Route::get('edit/category/{category}', [App\Http\Controllers\Api\CategoryController::class, 'edit']);
    Route::post('edit/category/{category}', [App\Http\Controllers\Api\CategoryController::class, 'update'])->can('update', 'category');
    Route::post('delete/category', [App\Http\Controllers\Api\CategoryController::class, 'destroy'])->can('delete', Category::class);
    Route::post('restore/category', [App\Http\Controllers\Api\CategoryController::class, 'restore'])->can('delete', Category::class);
    Route::get('/category', [App\Http\Controllers\Api\CategoryController::class, 'index']);

    // upload 
    Route::post('/upload/image', [App\Http\Controllers\Api\UploadController::class, 'store']);
    Route::post('/upload/video', [App\Http\Controllers\Api\UploadController::class, 'video']);

    // topage 
    Route::post('create/toppage', [App\Http\Controllers\Api\ToppageController::class, 'store'])->can('create', Toppage::class);
    Route::post('edit/toppage/{user}', [App\Http\Controllers\Api\ToppageController::class, 'update'])->can('update', Toppage::class);
    Route::get('edit/toppage/{user}', [App\Http\Controllers\Api\ToppageController::class, 'edit']);
    Route::post('change/toppage/status', [App\Http\Controllers\Api\ToppageController::class, 'changeStatus']);
    Route::post('update/toppage/detail', [App\Http\Controllers\Api\ToppageController::class, 'updateDetails']);

    // dashborad
    Route::get('/dashboard', [App\Http\Controllers\Api\DashboardController::class, 'dashBoard']);

    // photo
    Route::post('save', [App\Http\Controllers\Api\PhotoController::class, 'store']);
    Route::get('edit/photo/{photo}', [App\Http\Controllers\Api\PhotoController::class, 'edit']);
    Route::post('edit/photo/{photo}', [App\Http\Controllers\Api\PhotoController::class, 'update']);
    Route::delete('delete/photo/{photo}', [App\Http\Controllers\Api\PhotoController::class, 'destroy']);
});

Route::get('get/photo', [App\Http\Controllers\Api\PhotoController::class, 'index']);
Route::get('get/photo/{photo}', [App\Http\Controllers\Api\PhotoController::class, 'show']);
