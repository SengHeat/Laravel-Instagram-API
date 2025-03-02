<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReplyComment extends Model
{
    /** @use HasFactory<\Database\Factories\ReplyCommentFactory> */
    use HasFactory;

    protected $fillable = [
        'reply_comment',
        'user_id',
        'comment_id',
    ];

// Define the relationship for nested replies (replies to replies)
    public function parentReply(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ReplyComment::class, 'parent_comment_id');
    }

    // Define the relationship for replies (comments replying to this one)
    public function childReplies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReplyComment::class, 'parent_comment_id');
    }

}
