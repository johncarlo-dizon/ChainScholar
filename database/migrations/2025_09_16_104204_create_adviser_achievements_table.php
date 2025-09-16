<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('adviser_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adviser_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');                 // e.g., Best Research Mentor
            $table->string('issuer')->nullable();    // e.g., School/Organization
            $table->unsignedSmallInteger('year')->nullable();
            $table->text('description')->nullable(); // optional notes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adviser_achievements');
    }
};
