<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Display a listing of comments for a specific post.
     */
    public function index($postId)
    {
        // Retrieve comments for a specific post
        $comments = Post::findOrFail($postId)->comments;
        $post = Post::findOrFail($postId);

        // Count the likes for the post
        $likesCount = $post->likes()->count();

        return response()->json([
            'comments' => $comments,
            'like_counts' => $likesCount
        ]);
    }

    /**
     * Store a newly created comment for a specific post.
     */
    public function store(Request $request, $postId)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        // Find the post
        $post = Post::findOrFail($postId);

        // Create a new comment
        $comment = new Comment();
        $comment->comment = $request->input('comment');
        $comment->user_id = Auth::id();  // Get the ID of the authenticated user
        $comment->post_id = $post->id;

        // Save the comment
        $comment->save();

        return response()->json([
            'message' => 'Comment created successfully',
            'comment' => $comment,
        ], 201);
    }

    /**
     * Show a specific comment.
     */
    public function show($postId, $commentId)
    {
        $comment = Comment::where('post_id', $postId)
            ->findOrFail($commentId);

        return response()->json([
            'comment' => $comment,
        ]);
    }

    /**
     * Update a specific comment.
     */
    public function update(Request $request, $postId, $commentId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = Comment::where('post_id', $postId)
            ->findOrFail($commentId);

        // Ensure the user is the owner of the comment
        if ($comment->user_id != Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized to update this comment',
            ], 403);
        }

        // Update the comment
        $comment->content = $request->input('content');
        $comment->save();

        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => $comment,
        ]);
    }

    /**
     * Remove a specific comment.
     */
    public function destroy($postId, $commentId)
    {
        $comment = Comment::where('post_id', $postId)
            ->findOrFail($commentId);

        // Ensure the user is the owner of the comment
        if ($comment->user_id != Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized to delete this comment',
            ], 403);
        }

        // Delete the comment
        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }
}

