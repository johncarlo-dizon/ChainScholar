<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('adviser_profile_research_interest', function (Blueprint $table) {
            $table->id();

            // Short, explicit index names to avoid the 64-char limit
            $table->foreignId('adviser_profile_id')
                  ->constrained()
                  ->cascadeOnDelete()
                  ->index('apr_adviser_idx');

            $table->foreignId('research_interest_id')
                  ->constrained()
                  ->cascadeOnDelete()
                  ->index('apr_interest_idx');

            // Short unique constraint name
            $table->unique(
                ['adviser_profile_id', 'research_interest_id'],
                'apr_adviser_interest_uniq'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adviser_profile_research_interest');
    }
};
