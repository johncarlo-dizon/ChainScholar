<?php

// database/migrations/2025_08_16_000001_create_adviser_notes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('adviser_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('title_id')->constrained('titles')->cascadeOnDelete();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('adviser_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->longText('content');
            $table->timestamps();

            // One note per chapter per adviser (you can relax this if you want history rows)
            $table->unique(['document_id', 'adviser_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adviser_notes');
    }
};
