<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'short_bio' => $this->short_bio,
            'user_profile' => $this->user_profile
        ];
    }

    public static function tokenUser(string $token): array
    {
        return [
            'token' => $token,
        ];
    }

}
