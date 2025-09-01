<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // e.g. "Final Defense Schedule"
            $table->text('body');    // announcement details
            $table->date('event_date')->nullable(); // optional event date
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // admin who posted
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
