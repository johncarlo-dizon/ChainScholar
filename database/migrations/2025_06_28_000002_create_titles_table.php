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
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
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
            $table->timestamp('verified_at')->nullable();          // after automated/manual verification
            $table->timestamp('adviser_assigned_at')->nullable();  // when primary adviser is locked

            $table->enum('status', [
                'draft',
                'submitted',
                'verified',
                'awaiting_adviser',
                'in_advising',
                'returned',
                'archived'
            ])->default('draft');


            $table->foreignId('primary_adviser_id')->nullable()->constrained('users')->nullOnDelete();

        
            // Final document ID (add foreign key later)
            $table->unsignedBigInteger('final_document_id')->nullable();
        
            $table->timestamps();
         
            $table->index(['owner_id', 'status']);
            $table->unique(['owner_id','title']); // avoid duplicate exact titles per owner
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
