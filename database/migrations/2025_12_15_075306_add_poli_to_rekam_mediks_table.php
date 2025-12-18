<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // aman: kalau kolom sudah ada, tidak akan nambah lagi
        if (!Schema::hasColumn('rekam_mediks', 'poli')) {
            Schema::table('rekam_mediks', function (Blueprint $table) {
                $table->string('poli', 50)->nullable()->after('no_ktp');
            });
        }
    }

    public function down(): void
    {
        // aman: kalau kolom tidak ada, tidak ngapa-ngapain
        if (Schema::hasColumn('rekam_mediks', 'poli')) {
            Schema::table('rekam_mediks', function (Blueprint $table) {
                $table->dropColumn('poli');
            });
        }
    }
};
