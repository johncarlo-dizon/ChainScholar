<?php

// database/migrations/2025_09_23_000000_add_export_html_to_external_matches.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('external_plagiarism_matches', function (Blueprint $t) {
            $t->mediumText('export_html')->nullable()->after('your_excerpt');
        });
    }
    public function down(): void {
        Schema::table('external_plagiarism_matches', function (Blueprint $t) {
            $t->dropColumn('export_html');
        });
    }
};
