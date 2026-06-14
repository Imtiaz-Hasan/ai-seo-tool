<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One AI generation request (outline or draft) and its result - also the
 * "generation history". Runs through a queued job, so it carries a status.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('content_piece_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('draft');     // outline | draft
            $table->string('topic');
            $table->string('keyword')->nullable();
            $table->unsignedInteger('target_word_count')->default(1000);
            $table->string('status')->default('queued');   // queued | processing | done | failed
            $table->longText('result')->nullable();
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generations');
    }
};
