<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('blockchain_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('paper_id')->constrained('research_papers')->cascadeOnDelete();
            $t->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $t->enum('type', ['HASH','REGISTER','CONFIRM'])->index(); // what is requested
            $t->enum('status', ['PENDING','APPROVED','REFUSED'])->default('PENDING')->index();
            $t->text('reason')->nullable();              // refusal reason (optional)
            $t->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('acted_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('blockchain_requests');
    }
};
