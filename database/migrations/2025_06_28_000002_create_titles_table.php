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
        Schema::create('titles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->string('authors')->nullable();
        
            // Metadata
            $table->text('abstract')->nullable();
            $table->string('keywords')->nullable();
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->enum('research_type', ['Capstone', 'Thesis', 'Journal', 'Funded', 'Independent'])->default('Capstone');
            $table->string('ethics_clearance_no')->nullable();
            $table->text('review_comments')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'returned'])->default('draft');



        
            // Final document ID (add foreign key later)
            $table->unsignedBigInteger('finaldocument_id')->nullable();
        
            $table->timestamps();
        
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // DO NOT define finaldocument_id foreign key here yet!
        });
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('titles');
    }
};
