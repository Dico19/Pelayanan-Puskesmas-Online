<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            // ✅ jumlah berapa kali dilewati
            if (!Schema::hasColumn('antrians', 'skip_count')) {
                $table->unsignedTinyInteger('skip_count')
                    ->default(0)
                    ->after('status'); // pastikan kolom 'status' memang ada
            }

            // ✅ kapan terakhir kali dilewati
            if (!Schema::hasColumn('antrians', 'skipped_at')) {
                $table->timestamp('skipped_at')
                    ->nullable()
                    ->after('skip_count');
            }

            // ✅ kapan ditandai "tidak hadir"
            if (!Schema::hasColumn('antrians', 'absent_at')) {
                $table->timestamp('absent_at')
                    ->nullable()
                    ->after('skipped_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            // drop kolom dengan aman
            if (Schema::hasColumn('antrians', 'absent_at')) {
                $table->dropColumn('absent_at');
            }
            if (Schema::hasColumn('antrians', 'skipped_at')) {
                $table->dropColumn('skipped_at');
            }
            if (Schema::hasColumn('antrians', 'skip_count')) {
                $table->dropColumn('skip_count');
            }
        });
    }
};
