<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('role', 20)->nullable();                 // ADMIN / ADVISER / STUDENT
            $table->string('action');                               // e.g., "title.submitted", "adviser.accepted"
            $table->nullableMorphs('subject');                      // subject_type, subject_id
            $table->json('meta')->nullable();                       // free-form context (title, chapter, etc.)
            $table->string('ip', 64)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
            $table->index(['action', 'created_at']);
            $table->index(['role', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('activity_logs');
    }
};
