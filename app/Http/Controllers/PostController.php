<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{

    private function uploadImage(Request $request): ?string
    {
        $imagePath = null;

        if($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/posts');

            // Ensure the uploads directory exists
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Move the uploaded file to the correct folder
            $image->move($destinationPath, $name);

            // Now store the path to the uploaded image correctly
            $imagePath = 'uploads/posts/' . $name;
        }
        return $imagePath;
    }
    public function create(Request $request, string $userId): \Illuminate\Http\JsonResponse
    {
        $imagePath = null;

        $validator = Validator::make($request->all(), [
            'caption' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $post = $user->posts()->create([
            'caption' => $request->get('caption'),
            'image' => $this->uploadImage($request),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Post created successfully',
            'post' => $post,
        ], 201);
    }

    public function index()
    {
        // Fetch the latest posts and paginate (1 post per page in this example)
        $posts = Post::orderBy('created_at', 'desc')->paginate(10);

        // Add like count for each post
        $posts->getCollection()->transform(function ($post) {
            // Count likes for each post
            $post->like_counts = $post->likes()->count();
            $post->comment_counts = $post->comments()->count();
            return $post;
        });

        return response()->json([
            'is_last_page' => !$posts->hasMorePages(),  // Check if there's no next page
            'current_page' => $posts->currentPage(),    // Current page number
            'total_pages' => $posts->lastPage(),        // Total pages
            'per_page' => $posts->perPage(),            // Items per page
            'total_items' => $posts->total(),           // Total items
            'lists' => PostResource::collection($posts->items()),                 // The actual data of the current page
            'next_page_url' => $posts->nextPageUrl(),   // URL for the next page (null if no next page)
            'previous_page_url' => $posts->previousPageUrl(), // URL for the previous page (null if no previous page)
            'first_page_url' => $posts->url(1),         // URL for the first page
            'last_page_url' => $posts->url($posts->lastPage()) // URL for the last page
        ]);
    }



    public function getPostByUser(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if(!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Fetch posts created by the authenticated user
        $posts = $user->posts()->orderBy('created_at', 'desc')->paginate(10); // Optional: Paginate results

        return response()->json([
            'status' => 'success',
            'posts' => $posts->items(), // Access the 'data' part directly
            'current_page' => $posts->currentPage(),
            'total_pages' => $posts->lastPage(),
            'total_items' => $posts->total(),
            'per_page' => $posts->perPage(),
        ]);
    }
}
