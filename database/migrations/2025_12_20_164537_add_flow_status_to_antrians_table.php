<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            if (!Schema::hasColumn('antrians', 'flow_status')) {
                $table->string('flow_status', 30)->default('menunggu')->after('is_call');
            }

            if (!Schema::hasColumn('antrians', 'skip_count')) {
                $table->unsignedInteger('skip_count')->default(0)->after('flow_status');
            }

            if (!Schema::hasColumn('antrians', 'skipped_at')) {
                $table->timestamp('skipped_at')->nullable()->after('skip_count');
            }

            if (!Schema::hasColumn('antrians', 'absent_at')) {
                $table->timestamp('absent_at')->nullable()->after('skipped_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            if (Schema::hasColumn('antrians', 'flow_status')) $table->dropColumn('flow_status');
            if (Schema::hasColumn('antrians', 'skip_count')) $table->dropColumn('skip_count');
            if (Schema::hasColumn('antrians', 'skipped_at')) $table->dropColumn('skipped_at');
            if (Schema::hasColumn('antrians', 'absent_at')) $table->dropColumn('absent_at');
        });
    }
};
