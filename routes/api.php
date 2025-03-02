<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReplyCommentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:api')->post('user', [AuthController::class, 'getUser']);
Route::middleware('auth:api')->post('logout', [AuthController::class, 'logout']);
Route::middleware('auth:api')->post('update', [AuthController::class, 'update']);
Route::middleware('auth:api')->delete('delete', [AuthController::class, 'deleteUser']);

// ✅ Post Routes
Route::post('post-create/{userId}', [PostController::class, 'create']);
Route::post('posts', [PostController::class, 'index']);
Route::middleware('auth:api')->post('posts', [PostController::class, 'index']);
Route::middleware('auth:api')->post('post-user', [PostController::class, 'getPostByUser']);

// ✅ Comment Routes
Route::middleware('auth:api')->group(function () {
    Route::get('posts/{postId}/comments', [CommentController::class, 'index']);
    Route::post('posts/{postId}/comments', [CommentController::class, 'store']);
    Route::get('posts/{postId}/comments/{commentId}', [CommentController::class, 'show']);
    Route::put('posts/{postId}/comments/{commentId}', [CommentController::class, 'update']);
    Route::delete('posts/{postId}/comments/{commentId}', [CommentController::class, 'destroy']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('comments/{commentId}/replies', [ReplyCommentController::class, 'store']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('posts/{postId}/toggle-like', [LikeController::class, 'toggleLike']); // Toggle like/unlike on a post
    Route::get('posts/{postId}/likes-count', [LikeController::class, 'getLikesCount']); // Get likes count
    Route::get('posts/{postId}/is-liked', [LikeController::class, 'isLiked']); // Check if the post is liked by the user
});
/*
 <?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;

// ✅ Authentication Routes
Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');

    Route::middleware('auth:api')->group(function () {
        Route::get('user', 'getUser'); // Get Authenticated User
        Route::post('logout', 'logout');
        Route::put('update', 'update');
        Route::delete('delete', 'deleteUser');
    });
});

// ✅ Post Routes
Route::middleware('auth:api')->controller(PostController::class)->group(function () {
    Route::post('post-create/{userId}', 'create');
    Route::get('posts', 'index');
    Route::post('post-user', 'getPostByUser'); // Get User Posts
});

// ✅ Comment Routes
Route::middleware('auth:api')->prefix('posts/{postId}/comments')->controller(CommentController::class)->group(function () {
    Route::get('/', 'index'); // Get all comments for a post
    Route::post('/', 'store'); // Create a new comment
    Route::get('{commentId}', 'show'); // Get a specific comment
    Route::put('{commentId}', 'update'); // Update a comment
    Route::delete('{commentId}', 'destroy'); // Delete a comment
});

// ✅ Like Routes
Route::middleware('auth:api')->prefix('posts/{postId}')->controller(LikeController::class)->group(function () {
    Route::post('toggle-like', 'toggleLike'); // Like/Unlike a post
    Route::get('likes-count', 'getLikesCount'); // Get likes count
    Route::get('is-liked', 'isLiked'); // Check if the user liked the post
});
*/
