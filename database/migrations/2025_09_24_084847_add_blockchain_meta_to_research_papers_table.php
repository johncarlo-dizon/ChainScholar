<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('research_papers', function (Blueprint $t) {
            $t->char('sha256', 64)->nullable()->index();
            $t->string('file_disk', 50)->nullable()->after('file_path'); // 'public', 'local', 's3', etc.

            // blockchain meta
            $t->string('wallet', 64)->nullable()->index();        // 0x... address
            $t->string('tx_hash', 80)->nullable()->unique();      // 0x... tx
            $t->unsignedBigInteger('chain_id')->nullable()->index();
            $t->enum('chain_status', ['NONE','UPLOADED','REGISTERED','CONFIRMED','FAILED'])
              ->default('NONE')->index();
            $t->unsignedBigInteger('block_number')->nullable();
            $t->timestamp('confirmed_at')->nullable();
        });
    }

    public function down(): void {
        Schema::table('research_papers', function (Blueprint $t) {
            $t->dropColumn([
                'sha256','file_disk','wallet','tx_hash','chain_id',
                'chain_status','block_number','confirmed_at'
            ]);
        });
    }
};
