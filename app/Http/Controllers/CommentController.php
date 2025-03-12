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
    public function index($postId): \Illuminate\Http\JsonResponse
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

                // Load the reply comments for the current comment using `replies` method
                $replyComments = $comment->replies(); // If you're using `replies` in the model

                // Check if there are reply comments and transform them
                if ($replyComments->isNotEmpty()) {
                    $comment->replies = $replyComments->map(function ($reply) {
                        // Load the related user for the reply
                        $user = $reply->user;
                        unset($reply->user);

                        // Add the user's ID, name, and profile image to the reply comment response
                        $reply->user = UserResource::userIdNameAndProfile($user);

                        return $reply;
                    });
                } else {
                    $comment->replies = []; // Ensure the key is present even if there are no replies
                }

                return $comment;
            });

            // Return the paginated comments with user information and reply comments
            return $this->paginateData($comments, $comments->items());

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle case where the post was not found
            return $this->sendError(code: 404, msg: "Post not found");
        } catch (\Exception $e) {
            // Handle any other exceptions
            return $this->sendError(code: 500, msg: "An error occurred while retrieving comments: " . $e->getMessage());
        }
    }


    /**
     * Store a newly created comment for a specific post.
     */
    public function store(Request $request, $postId): \Illuminate\Http\JsonResponse
    {
        // Validate incoming request
        $request->validate([
            'comment' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        // Find the post
        $post = Post::findOrFail($postId);
        $user = auth()->user();

        // Create the new comment
        $comment = Comment::create([
            "post_id" => $post->id,
            "user_id" => $user->id,
            "comment" => $request->get('comment'),
            "parent_id" => $request->get('parent_id') ?? null
        ]);

        return $this->sendResponse($comment, code: 201, msg: "Comment created successfully");
    }

    /**
     * Show a specific comment.
     */
    public function show($postId, $commentId)
    {
        try {
            $comment = Comment::where('post_id', $postId)
                ->findOrFail($commentId);

            return response()->json([
                'status' => 200,
                'message' => 'Comment retrieved successfully',
                'data' => $comment,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendError(code: 404, msg: "Comment not found");
        }
    }

    /**
     * Update a specific comment.
     */
    public function update(Request $request, $postId, $commentId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        try {
            $comment = Comment::where('post_id', $postId)
                ->findOrFail($commentId);

            // Ensure the user is the owner of the comment
            if ($comment->user_id != Auth::id()) {
                return $this->sendError(code: 403, msg: 'Unauthorized to update this comment');
            }

            // Update the comment content
            $comment->comment = $request->input('content');
            $comment->save();

            return $this->sendResponse($comment, code: 200, msg: 'Comment updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendError(code: 404, msg: 'Comment not found');
        } catch (\Exception $e) {
            return $this->sendError(code: 500, msg: "An error occurred while updating the comment: " . $e->getMessage());
        }
    }

    /**
     * Remove a specific comment.
     */
    public function destroy($postId, $commentId)
    {
        try {
            $comment = Comment::where('post_id', $postId)
                ->findOrFail($commentId);

            // Ensure the user is the owner of the comment
            if ($comment->user_id != Auth::id()) {
                return $this->sendError(code: 403, msg: 'Unauthorized to delete this comment');
            }

            // Delete the comment
            $comment->delete();

            return $this->sendResponse([], code: 200, msg: 'Comment deleted successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendError(code: 404, msg: 'Comment not found');
        } catch (\Exception $e) {
            return $this->sendError(code: 500, msg: 'An error occurred while deleting the comment: ' . $e->getMessage());
        }
    }
}
