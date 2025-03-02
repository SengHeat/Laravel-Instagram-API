<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'caption' => $this->caption,
            'image' => $this->image,
            'user_id' => $this->user_id,
            'comment_count' => $this->comment_counts,
            'like_counts' => $this->like_counts,  // Assuming you're using `withCount('likes')`
            'is_liked' => $this->is_liked
        ];
    }
}
