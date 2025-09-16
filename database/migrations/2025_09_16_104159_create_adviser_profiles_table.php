<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('adviser_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Department / Field of Expertise
            $table->string('department')->nullable();              // e.g., Computer Engineering Dept
            $table->string('field_of_expertise')->nullable();      // e.g., Distributed Systems

            // Educational Background (highest degree, school, year)
            $table->string('highest_degree')->nullable();          // e.g., MS, PhD
            $table->string('degree_school')->nullable();           // e.g., UP Diliman
            $table->unsignedSmallInteger('degree_year')->nullable();

            // Advisory Experience (years or number of projects handled)
            $table->unsignedSmallInteger('advisory_years')->nullable();       // years
            $table->unsignedSmallInteger('projects_handled')->nullable();     // count

            // Optional short bio/notes
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adviser_profiles');
    }
};
