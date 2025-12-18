<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // kalau tabel belum ada (jaga-jaga)
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('action', 30);
                $table->unsignedBigInteger('antrian_id')->nullable();

                $table->unsignedBigInteger('dokter_id')->nullable();
                $table->string('dokter_name', 150)->nullable();

                $table->string('poli', 60)->nullable();

                $table->string('no_antrian', 30)->nullable();
                $table->string('no_ktp', 30)->nullable();
                $table->string('pasien_nama', 150)->nullable();

                $table->json('changes')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();

                $table->timestamps();
            });

            return;
        }

        // tabel sudah ada → tambahkan kolom yang kurang
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'antrian_id')) {
                $table->unsignedBigInteger('antrian_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('audit_logs', 'dokter_id')) {
                $table->unsignedBigInteger('dokter_id')->nullable()->after('antrian_id');
            }
            if (!Schema::hasColumn('audit_logs', 'dokter_name')) {
                $table->string('dokter_name', 150)->nullable()->after('dokter_id');
            }
            if (!Schema::hasColumn('audit_logs', 'poli')) {
                $table->string('poli', 60)->nullable()->after('dokter_name');
            }
            if (!Schema::hasColumn('audit_logs', 'no_antrian')) {
                $table->string('no_antrian', 30)->nullable()->after('poli');
            }
            if (!Schema::hasColumn('audit_logs', 'no_ktp')) {
                $table->string('no_ktp', 30)->nullable()->after('no_antrian');
            }
            if (!Schema::hasColumn('audit_logs', 'pasien_nama')) {
                $table->string('pasien_nama', 150)->nullable()->after('no_ktp');
            }
            if (!Schema::hasColumn('audit_logs', 'changes')) {
                $table->json('changes')->nullable()->after('pasien_nama');
            }
            if (!Schema::hasColumn('audit_logs', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('changes');
            }
            if (!Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('audit_logs', 'action')) {
                $table->string('action', 30)->default('')->after('id');
            }
            if (!Schema::hasColumn('audit_logs', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        // biarin aja (aman), biasanya audit log tidak perlu rollback kolom.
    }
};
