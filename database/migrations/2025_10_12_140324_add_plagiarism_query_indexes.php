<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add indexes for faster plagiarism queries
        Schema::table('titles', function (Blueprint $table) {
            $table->index(['status', 'final_document_id']);
            $table->index(['created_at']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->index(['title_id']);
            $table->index(['created_at']);
        });

        Schema::table('research_papers', function (Blueprint $table) {
            $table->index(['created_at']);
            // Removed the extracted_text index - it's not efficient
        });

        // Optional: Add this for even better performance on research_papers
        Schema::table('research_papers', function (Blueprint $table) {
            $table->index(['created_at', 'id']); // Composite index for ordering
        });
    }

    public function down(): void
    {
        Schema::table('titles', function (Blueprint $table) {
            $table->dropIndex(['status', 'final_document_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['title_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('research_papers', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['created_at', 'id']);
        });
    }
};