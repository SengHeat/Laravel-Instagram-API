<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    private function uploadUserProfile(UserRequest $request): ?string
    {
        $imagePath = null;

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
        return $imagePath;
    }
    public function register(UserRequest $request): \Illuminate\Http\JsonResponse
    {

        // The request is already validated by the UserRequest class
        $validated = $request->validated();

        Log::info($validated['name']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_profile' => $this->uploadUserProfile($request),
            'short_bio' => $validated['short_bio'],
        ]);

        $token = $user->createToken('AuthToken')->accessToken;
        return $this->sendResponse(UserResource::tokenUser($token));
    }

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->only(['email', 'password']), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if($validator->fails()) {
            return $this->sendError($validator->errors(),msg: "error");
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->accessToken;

            return $this->sendResponse(UserResource::tokenUser($token));

        }

        // Authentication failed
        return $this->sendError(msg: "Invalid credentials");
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

        return $this->sendResponse(UserResource::make($user));
    }

    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        // Check if the user is authenticated
        if (!$user) {
            return $this->sendError(msg: "User not authenticated");
        }

        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,',
            'short_bio' => 'sometimes|string|max:255',
            // Add other fields you want to allow the user to update
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update the user data if validation passes
        $user->name = $request->input('name', $user->name);  // Update name if provided, otherwise keep existing value
        $user->short_bio = $request->input('short_bio', $user->short_bio);  // Update short bio
//        $newImagePath = $this->uploadUserProfile($request);
//        // Add any other fields you want to update
//        if ($newImagePath) {
//            // Delete old profile image if exists
//            if ($user->user_profile) {
//                Storage::disk('public')->delete($user->user_profile);
//            }
//            $user->user_profile = $newImagePath;
//        }
        // Save the updated user
        $user->save();

        // Return updated user data or a success message
        return $this->sendResponse(UserResource::make($user));
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
