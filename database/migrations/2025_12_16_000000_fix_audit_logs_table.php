<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('antrian_id')->nullable()->index();
                $table->unsignedBigInteger('dokter_id')->nullable()->index();
                $table->string('dokter_nama')->nullable()->index();

                $table->string('no_ktp', 40)->nullable()->index();
                $table->string('pasien_nama')->nullable()->index();
                $table->string('no_antrian', 20)->nullable()->index();
                $table->string('poli')->nullable()->index();

                $table->string('action', 30)->index(); // dipanggil/mulai/selesai/lewati/panggil_ulang
                $table->longText('before')->nullable(); // JSON string
                $table->longText('after')->nullable();  // JSON string

                $table->string('ip', 60)->nullable();
                $table->string('user_agent', 500)->nullable();

                $table->timestamps();
            });

            return;
        }

        // Kalau tabel sudah ada, tambahkan kolom yang belum ada
        $cols = [
            'antrian_id'   => fn(Blueprint $t) => $t->unsignedBigInteger('antrian_id')->nullable()->index(),
            'dokter_id'    => fn(Blueprint $t) => $t->unsignedBigInteger('dokter_id')->nullable()->index(),
            'dokter_nama'  => fn(Blueprint $t) => $t->string('dokter_nama')->nullable()->index(),
            'no_ktp'       => fn(Blueprint $t) => $t->string('no_ktp', 40)->nullable()->index(),
            'pasien_nama'  => fn(Blueprint $t) => $t->string('pasien_nama')->nullable()->index(),
            'no_antrian'   => fn(Blueprint $t) => $t->string('no_antrian', 20)->nullable()->index(),
            'poli'         => fn(Blueprint $t) => $t->string('poli')->nullable()->index(),
            'action'       => fn(Blueprint $t) => $t->string('action', 30)->nullable()->index(),
            'before'       => fn(Blueprint $t) => $t->longText('before')->nullable(),
            'after'        => fn(Blueprint $t) => $t->longText('after')->nullable(),
            'ip'           => fn(Blueprint $t) => $t->string('ip', 60)->nullable(),
            'user_agent'   => fn(Blueprint $t) => $t->string('user_agent', 500)->nullable(),
        ];

        foreach ($cols as $name => $adder) {
            if (!Schema::hasColumn('audit_logs', $name)) {
                Schema::table('audit_logs', function (Blueprint $table) use ($adder) {
                    $adder($table);
                });
            }
        }
    }

    public function down(): void
    {
        // tidak drop (biar aman)
    }
};
