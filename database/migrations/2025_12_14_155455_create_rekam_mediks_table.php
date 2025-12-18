<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekam_mediks', function (Blueprint $table) {
            $table->id();

            // relasi (aman dulu: nullable + nullOnDelete)
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->foreignId('dokter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('antrian_id')->nullable()->constrained('antrians')->nullOnDelete();

            // identitas tambahan (biar gampang cari riwayat via NIK)
            $table->string('no_ktp', 32)->nullable()->index();
            $table->string('poli_code', 50)->nullable()->index();
            $table->date('tanggal_kunjungan')->index();

            // isi rekam medis
            $table->text('keluhan')->nullable();
            $table->text('diagnosa')->nullable();
            $table->text('resep')->nullable();     // misal: "Amoxicillin 500mg 3x1"
            $table->text('catatan')->nullable();   // misal: "Kontrol 3 hari, tebus obat di apotek"

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekam_mediks');
    }
};
