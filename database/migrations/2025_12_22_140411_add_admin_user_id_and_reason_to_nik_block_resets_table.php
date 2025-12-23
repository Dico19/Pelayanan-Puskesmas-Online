<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('nik_block_resets', function (Blueprint $table) {
            if (!Schema::hasColumn('nik_block_resets', 'admin_user_id')) {
                $table->unsignedBigInteger('admin_user_id')->nullable()->after('no_ktp');
            }
            if (!Schema::hasColumn('nik_block_resets', 'reason')) {
                $table->string('reason', 255)->nullable()->after('admin_user_id');
            }

            // optional FK (kalau users id bigint)
            // $table->foreign('admin_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('nik_block_resets', function (Blueprint $table) {
            // optional drop FK dulu kalau kamu aktifkan FK
            // $table->dropForeign(['admin_user_id']);

            if (Schema::hasColumn('nik_block_resets', 'reason')) {
                $table->dropColumn('reason');
            }
            if (Schema::hasColumn('nik_block_resets', 'admin_user_id')) {
                $table->dropColumn('admin_user_id');
            }
        });
    }
};
