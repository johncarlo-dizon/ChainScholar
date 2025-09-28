<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('title_id')->constrained('titles')->cascadeOnDelete();

            $table->string('chapter')->nullable();
            $table->longText('content')->nullable();

            $table->enum('format', ['separate', 'combined'])->default('separate');
            $table->string('file_path')->nullable();

            // Legacy single score (kept for compatibility)
            $table->decimal('plagiarism_score', 5, 2)->nullable();

            // NEW: store both internal & external scores (0–100 with 2 decimal places)
            $table->decimal('plagiarism_internal', 5, 2)->nullable();
            $table->decimal('plagiarism_external', 5, 2)->nullable();

            $table->timestamps();

            $table->index(['title_id', 'chapter']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
