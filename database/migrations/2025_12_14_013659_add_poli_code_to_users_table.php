<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Tambah kolom poli_code kalau belum ada
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'poli_code')) {
                $table->string('poli_code', 50)->nullable()->after('role_id');
            }
        });

        // 2) Tambah foreign key role_id -> roles.id kalau belum ada
        // (pakai cek information_schema biar aman, gak double)
        if (Schema::hasColumn('users', 'role_id')) {
            $fkExists = DB::selectOne("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'users'
                  AND COLUMN_NAME = 'role_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
                LIMIT 1
            ");

            if (!$fkExists) {
                Schema::table('users', function (Blueprint $table) {
                    $table->foreign('role_id')
                        ->references('id')
                        ->on('roles')
                        ->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        // drop FK kalau ada
        $fkExists = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'users'
              AND COLUMN_NAME = 'role_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");

        if ($fkExists) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
            });
        }

        // drop kolom poli_code kalau ada
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'poli_code')) {
                $table->dropColumn('poli_code');
            }
        });
    }
};
