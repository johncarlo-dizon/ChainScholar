<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('adviser_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('title_id')->constrained('titles')->cascadeOnDelete();
            $table->foreignId('adviser_id')->constrained('users')->cascadeOnDelete();

            // Who initiated: student picked an adviser, or adviser volunteered
            $table->enum('requested_by', ['student','adviser']);

            // Request lifecycle:
            // pending  -> accepted/declined
            // withdrawn (by requester) or expired (optional, if you auto-expire)
            $table->enum('status', ['pending','accepted','declined','withdrawn','expired'])->default('pending');

            $table->text('message')->nullable();

            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            // Prevent duplicate active requests (one open request between a title & adviser)
            $table->unique(['title_id','adviser_id','status'], 'title_adviser_status_unique');
            $table->index(['adviser_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adviser_requests');
    }
};
