<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->string('action', 30); // panggil|mulai|selesai|lewati
            $table->unsignedBigInteger('antrian_id')->nullable();

            $table->unsignedBigInteger('dokter_id')->nullable();
            $table->string('dokter_name', 150)->nullable();

            $table->string('poli', 60)->nullable();

            $table->string('no_antrian', 30)->nullable();
            $table->string('no_ktp', 30)->nullable();
            $table->string('pasien_nama', 150)->nullable();

            $table->json('changes')->nullable(); // before/after snapshot
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['action', 'created_at']);
            $table->index(['poli', 'created_at']);
            $table->index('no_ktp');
            $table->index('dokter_id');
            $table->index('antrian_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
