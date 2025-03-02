<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\ReplyComment;
use Illuminate\Http\Request;

class ReplyCommentController extends Controller
{
    public function store(Request $request, $commentId): \Illuminate\Http\JsonResponse
    {
        try{
            $request->validate([
                'reply_comment' => 'required|string|max:1000',
            ]);

            $comment = Comment::findOrFail($commentId);

            $reply = ReplyComment::create([
                'user_id' => auth()->id(), // Assuming the user is authenticated
                'comment_id' => $comment->id, // The comment being replied to
                'reply_comment' => $request->get('reply_comment'), // The reply text
            ]);

            return $this->sendResponse($reply);

        }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle case where the post was not found
            return $this->sendError(code: 404, msg: "Post not found");
        } catch (\Exception $e) {
            // Handle any other exceptions
            return $this->sendError(code: 500, msg: "An error occurred while retrieving comments $e");
        }
    }
}
