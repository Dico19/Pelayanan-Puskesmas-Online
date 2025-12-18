<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('antrians', function (Blueprint $table) {

            // call_token
            if (!Schema::hasColumn('antrians', 'call_token')) {
                if (Schema::hasColumn('antrians', 'is_call')) {
                    $table->unsignedBigInteger('call_token')->default(0)->after('is_call');
                } else {
                    $table->unsignedBigInteger('call_token')->default(0);
                }
            }

            // call_at
            if (!Schema::hasColumn('antrians', 'call_at')) {
                $table->timestamp('call_at')->nullable()->after('call_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            if (Schema::hasColumn('antrians', 'call_at')) {
                $table->dropColumn('call_at');
            }
            if (Schema::hasColumn('antrians', 'call_token')) {
                $table->dropColumn('call_token');
            }
        });
    }
};
