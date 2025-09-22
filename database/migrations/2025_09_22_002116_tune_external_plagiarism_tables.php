<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('external_plagiarism_matches', function (Blueprint $t) {
            // 1) widen URL
            // If you're on MySQL/MariaDB: this becomes VARCHAR(2048)
            $t->string('source_url', 2048)->nullable()->change();

            // 2) optional: drop unused source_excerpt
            // Comment this out if you prefer to keep the column.
            if (Schema::hasColumn('external_plagiarism_matches', 'source_excerpt')) {
                $t->dropColumn('source_excerpt');
            }

            // 3) composite index for frequent read path
            $t->index(['scan_id_fk', 'percent'], 'idx_matches_scan_percent');
        });
    }

    public function down(): void
    {
        Schema::table('external_plagiarism_matches', function (Blueprint $t) {
            // revert URL width
            $t->string('source_url')->nullable()->change();

            // restore dropped column (only if you dropped it)
            // NOTE: longText is what you had originally
            $t->longText('source_excerpt')->nullable();

            // drop the composite index
            $t->dropIndex('idx_matches_scan_percent');
        });
    }
};
