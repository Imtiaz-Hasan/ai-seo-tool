<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_pieces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('target_keyword')->nullable();
            $table->unsignedInteger('target_word_count')->default(1000);
            $table->longText('body')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->unsignedTinyInteger('last_score')->nullable(); // cached SEO score 0-100
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_pieces');
    }
};
