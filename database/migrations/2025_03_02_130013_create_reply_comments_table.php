<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reply_comments', function (Blueprint $table) {
            $table->id();
            $table->string('reply_comment');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User who made the reply
            $table->foreignId('comment_id')->constrained()->onDelete('cascade'); // Comment being replied to
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reply_comments');
    }
};
