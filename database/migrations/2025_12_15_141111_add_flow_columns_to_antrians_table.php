<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            if (!Schema::hasColumn('antrians', 'status')) {
                // status: menunggu -> dipanggil -> dilayani -> selesai
                $table->string('status', 20)->default('menunggu')->after('is_call');

                $table->timestamp('called_at')->nullable()->after('status');
                $table->timestamp('started_at')->nullable()->after('called_at');
                $table->timestamp('finished_at')->nullable()->after('started_at');
                $table->timestamp('skipped_at')->nullable()->after('finished_at');

                $table->index(['poli', 'tanggal_antrian', 'status'], 'antrians_poli_tgl_status_idx');
            }
        });

        // Backfill aman: data lama yang sudah is_call=1 kita anggap "dipanggil"
        DB::table('antrians')
            ->where('is_call', 1)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'menunggu');
            })
            ->update(['status' => 'dipanggil']);
    }

    public function down(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            if (Schema::hasColumn('antrians', 'status')) {
                $table->dropIndex('antrians_poli_tgl_status_idx');
                $table->dropColumn(['status', 'called_at', 'started_at', 'finished_at', 'skipped_at']);
            }
        });
    }
};
