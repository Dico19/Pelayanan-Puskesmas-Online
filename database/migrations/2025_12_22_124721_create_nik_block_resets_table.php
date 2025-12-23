<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nik_block_resets', function (Blueprint $table) {
            $table->id();
            $table->string('no_ktp', 16)->index();
            $table->unsignedBigInteger('admin_user_id')->nullable()->index();
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->foreign('admin_user_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nik_block_resets');
    }
};
