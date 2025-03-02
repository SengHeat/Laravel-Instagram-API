<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
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
        try {
            // Retrieve the post to ensure it exists
            $post = Post::findOrFail($postId);

            // Paginate the comments for the post (10 comments per page)
            $comments = $post->comments()->paginate(10);

            // Loop through the comments and attach the user's ID, name, and profile image, along with reply comments
            $comments->getCollection()->transform(function ($comment) {
                // Load the related user for the comment
                $user = $comment->user;
                unset($comment->user);

                // Add the user's ID, name, and profile image to the comment response using UserResource
                $comment->user = UserResource::userIdNameAndProfile($user);

                // Load the reply comments for the current comment
                $replyComments = $comment->replyComments;

                // Loop through each reply comment and attach the user's details (id, name, and profile image)
                $comment->reply_comment = $replyComments->map(function ($reply) {
                    // Load the related user for the reply
                    $user = $reply->user;
                    unset($reply->user);

                    // Add the user's ID, name, and profile image to the reply comment response
                    $reply->user = UserResource::userIdNameAndProfile($user);

                    return $reply;
                });

                return $comment;
            });

            // Return the paginated comments with user information and reply comments
            return $this->paginateData($comments, $comments->items());

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle case where the post was not found
            return $this->sendError(code: 404, msg: "Post not found");
        } catch (\Exception $e) {
            // Handle any other exceptions
            return $this->sendError(code: 500, msg: "An error occurred while retrieving comments $e");
        }
    }

    /**
     * Store a newly created comment for a specific post.
     */
    public function store(Request $request, $postId): \Illuminate\Http\JsonResponse
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

        return  $this->sendResponse($comment, code: 200, msg: "Comment created successfully");
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

