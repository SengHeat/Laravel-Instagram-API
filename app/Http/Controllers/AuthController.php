<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $imagePath = null;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',  // Ensure password confirmation field exists
            'user_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',  // Validate image (optional)
            'short_bio' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 400);  // Return 400 Bad Request if validation fails
        }

        //handle file upload
        if($request->hasFile('user_profile')) {
            $image = $request->file('user_profile');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/users');

            // Ensure the uploads directory exists
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Move the uploaded file to the correct folder
            $image->move($destinationPath, $name);

            // Now store the path to the uploaded image correctly
            $imagePath = 'uploads/users/' . $name;
        }


        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'user_profile' => $imagePath,
            'short_bio' => $request->get('short_bio'),
        ]);

        $token = $user->createToken('AuthToken')->accessToken;

        // Return response with the token and user data
        return response()->json([
            'token' => $token,
            'user' => $user
        ], 201);  // Status code 201 for Created
    }

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->only(['email', 'password']), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->accessToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
            ], 200);
        }

        // Authentication failed
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials',
        ], 401);
    }

    private function findUser(): \Illuminate\Http\JsonResponse|\Illuminate\Contracts\Auth\Authenticatable
    {
        $user = Auth::user(); // Gets user from Bearer token

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return $user;
    }

    public function getUser(): \Illuminate\Http\JsonResponse
    {
        $user = $this->findUser();

        return response()->json([
            'user' => UserResource::make($user),
        ], 200);
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if ($user) {
            // Revoke the current access token
            $request->user()->token()->revoke();

            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully'
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'User not found'
        ], 404);
    }

    public function deleteUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user(); // Get the authenticated user

        if ($user) {
            // Revoke the current access token
            $request->user()->token()->revoke();

            // Optionally delete the user from the database
            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully and logged out'
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'User not found'
        ], 404);
    }


}
