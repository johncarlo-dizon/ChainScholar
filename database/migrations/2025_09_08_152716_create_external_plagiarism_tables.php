<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('external_plagiarism_scans', function (Blueprint $t) {
      $t->id();
      $t->unsignedBigInteger('document_id')->index();
      $t->uuid('scan_id')->unique();
      $t->string('status')->default('queued'); // queued|running|completed|exported|error
      $t->unsignedInteger('score')->default(0); // max % among matches
      $t->unsignedInteger('credits_used')->nullable();
      $t->boolean('sandbox')->default(true);    // ← NEW
      $t->json('raw_payload')->nullable();      // ← NEW (last webhook payload)
      $t->text('error_message')->nullable();
      $t->timestamps();
    });

    Schema::create('external_plagiarism_matches', function (Blueprint $t) {
      $t->id();
      $t->unsignedBigInteger('scan_id_fk')->index();
      $t->unsignedBigInteger('document_id')->index();
      $t->unsignedInteger('percent')->default(0);
      $t->string('source_title')->nullable();
      $t->string('source_url')->nullable();
      $t->longText('your_excerpt')->nullable();
      $t->longText('source_excerpt')->nullable();
      $t->timestamps();
      $t->string('export_key', 191)->nullable()->index();

      $t->foreign('scan_id_fk')
        ->references('id')
        ->on('external_plagiarism_scans')
        ->cascadeOnDelete();
    });
  }

  public function down(): void {
    Schema::dropIfExists('external_plagiarism_matches');
    Schema::dropIfExists('external_plagiarism_scans');
  }
};
