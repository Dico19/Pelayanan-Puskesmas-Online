<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            if (!Schema::hasColumn('antrians', 'is_skipped')) {
                $table->boolean('is_skipped')->default(0)->after('is_call');
            }
        });
    }

    public function down(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            if (Schema::hasColumn('antrians', 'is_skipped')) {
                $table->dropColumn('is_skipped');
            }
        });
    }
};
