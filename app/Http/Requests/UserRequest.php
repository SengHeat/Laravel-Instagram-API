<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Set to true if the user is authorized to make this request
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255', // Name is required and a string
            'email' => 'required|email|unique:users,email|max:255', // Unique email and proper format
            'password' => 'required|string|min:8|confirmed', // Password is required and must match the confirmation
            'user_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'short_bio' => 'nullable|string|max:500', // Short bio (optional)
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'The email address is already taken.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'user_profile.max' => 'Profile URL should not exceed 255 characters.',
            'short_bio.max' => 'Short bio should not exceed 500 characters.',
        ];
    }
}
