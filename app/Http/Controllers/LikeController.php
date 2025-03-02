<?php

// app/Http/Controllers/LikeController.php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * Like or Unlike a post (toggle functionality).
     */
    public function toggleLike($postId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated.',
            ], 401);
        }

        // Find the post by its ID
        $post = Post::findOrFail($postId);

        // Check if the user has already liked the post
        $like = Like::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($like) {
            // If the user has liked the post, unlike it (delete the like)
            $like->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Post unliked successfully.',
            ], 200);
        } else {
            // If the user has not liked the post, like it (create a new like)
            $like = new Like();
            $like->user_id = $user->id;
            $like->post_id = $post->id;
            $like->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Post liked successfully.',
                'like' => $like,
            ], 200);
        }
    }

    /**
     * Get the like count for a post.
     */
    public function getLikesCount($postId)
    {
        $post = Post::findOrFail($postId);

        // Count the likes for the post
        $likesCount = $post->likes()->count();

        return response()->json([
            'likes_count' => $likesCount,
        ], 200);
    }

    /**
     * Check if the user has liked a post.
     */
    public function isLiked($postId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated.',
            ], 401);
        }

        $post = Post::findOrFail($postId);

        // Check if the user has already liked the post
        $hasLiked = $post->likes()->where('user_id', $user->id)->exists();

        return response()->json([
            'has_liked' => $hasLiked,
        ], 200);
    }
}
