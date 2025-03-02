<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:api')->post('user', [AuthController::class, 'getUser']);
Route::middleware('auth:api')->post('logout', [AuthController::class, 'logout']);
Route::middleware('auth:api')->delete('delete', [AuthController::class, 'deleteUser']);

Route::post('post-create/{userId}', [PostController::class, 'create']);
Route::post('posts', [PostController::class, 'index']);
Route::middleware('auth:api')->post('post-user', [PostController::class, 'getPostByUser']);


Route::middleware('auth:api')->group(function () {
    Route::get('posts/{postId}/comments', [CommentController::class, 'index']);
    Route::post('posts/{postId}/comments', [CommentController::class, 'store']);
    Route::get('posts/{postId}/comments/{commentId}', [CommentController::class, 'show']);
    Route::put('posts/{postId}/comments/{commentId}', [CommentController::class, 'update']);
    Route::delete('posts/{postId}/comments/{commentId}', [CommentController::class, 'destroy']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('posts/{postId}/toggle-like', [LikeController::class, 'toggleLike']); // Toggle like/unlike on a post
    Route::get('posts/{postId}/likes-count', [LikeController::class, 'getLikesCount']); // Get likes count
    Route::get('posts/{postId}/is-liked', [LikeController::class, 'isLiked']); // Check if the post is liked by the user
});
