<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            // status: menunggu | dipanggil | dilayani | selesai | dilewati
            if (!Schema::hasColumn('antrians', 'status')) {
                $table->string('status', 20)->default('menunggu')->after('is_call');
            }
            if (!Schema::hasColumn('antrians', 'called_at')) {
                $table->dateTime('called_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('antrians', 'started_at')) {
                $table->dateTime('started_at')->nullable()->after('called_at');
            }
            if (!Schema::hasColumn('antrians', 'finished_at')) {
                $table->dateTime('finished_at')->nullable()->after('started_at');
            }
            if (!Schema::hasColumn('antrians', 'skipped_at')) {
                $table->dateTime('skipped_at')->nullable()->after('finished_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            foreach (['status','called_at','started_at','finished_at','skipped_at'] as $col) {
                if (Schema::hasColumn('antrians', $col)) $table->dropColumn($col);
            }
        });
    }
};
