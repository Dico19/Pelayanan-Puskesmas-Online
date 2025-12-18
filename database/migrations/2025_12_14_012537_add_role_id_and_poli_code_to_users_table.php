<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        // Pastikan InnoDB (FK butuh InnoDB)
        try { DB::statement("ALTER TABLE `users` ENGINE=InnoDB"); } catch (\Throwable $e) {}
        if (Schema::hasTable('roles')) {
            try { DB::statement("ALTER TABLE `roles` ENGINE=InnoDB"); } catch (\Throwable $e) {}
        }

        // Ambil tipe roles.id (INT/BIGINT) supaya role_id match 100%
        $rolesIdType = null;
        if (Schema::hasTable('roles')) {
            $rolesIdType = DB::selectOne("
                SELECT COLUMN_TYPE
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'roles'
                  AND COLUMN_NAME = 'id'
                LIMIT 1
            ");
        }

        // default BIGINT kalau roles belum ada (tapi FK akan dipasang kalau roles sudah ada)
        $wantsBigInt = $rolesIdType
            ? str_contains(strtolower($rolesIdType->COLUMN_TYPE), 'bigint')
            : true;

        $hasRoleId  = Schema::hasColumn('users', 'role_id');
        $hasPoliCode = Schema::hasColumn('users', 'poli_code');

        // 1) Tambah kolom kalau belum ada
        Schema::table('users', function (Blueprint $table) use ($hasRoleId, $hasPoliCode, $wantsBigInt) {
            if (!$hasRoleId) {
                if ($wantsBigInt) {
                    $table->unsignedBigInteger('role_id')->nullable()->after('id');
                } else {
                    $table->unsignedInteger('role_id')->nullable()->after('id');
                }
            }

            if (!$hasPoliCode) {
                $table->string('poli_code', 50)->nullable()->after('role_id');
            }
        });

        // 2) Kalau role_id sudah ada tapi tipenya beda, samakan dulu (biar FK tidak error)
        if (Schema::hasTable('roles')) {
            $userRoleIdType = DB::selectOne("
                SELECT COLUMN_TYPE
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'users'
                  AND COLUMN_NAME = 'role_id'
                LIMIT 1
            ");

            if ($userRoleIdType) {
                $userIsBigInt = str_contains(strtolower($userRoleIdType->COLUMN_TYPE), 'bigint');

                if ($userIsBigInt !== $wantsBigInt) {
                    $sqlType = $wantsBigInt ? 'BIGINT UNSIGNED' : 'INT UNSIGNED';
                    DB::statement("ALTER TABLE `users` MODIFY `role_id` {$sqlType} NULL");
                }
            }

            // 3) Pasang FK kalau belum ada
            $fk = DB::selectOne("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'users'
                  AND COLUMN_NAME = 'role_id'
                  AND REFERENCED_TABLE_NAME = 'roles'
                LIMIT 1
            ");

            if (!$fk) {
                Schema::table('users', function (Blueprint $table) {
                    $table->foreign('role_id', 'users_role_id_foreign')
                        ->references('id')
                        ->on('roles')
                        ->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        // drop FK kalau ada
        $fk = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'users'
              AND COLUMN_NAME = 'role_id'
              AND REFERENCED_TABLE_NAME = 'roles'
            LIMIT 1
        ");

        if ($fk) {
            Schema::table('users', function (Blueprint $table) use ($fk) {
                $table->dropForeign($fk->CONSTRAINT_NAME);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'poli_code')) {
                $table->dropColumn('poli_code');
            }
            // kalau mau drop role_id juga, buka komentar ini:
            // if (Schema::hasColumn('users', 'role_id')) $table->dropColumn('role_id');
        });
    }
};
