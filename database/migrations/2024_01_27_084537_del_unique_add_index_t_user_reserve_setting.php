<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ユニークキー削除　index追加
        Schema::table('t_user_reserve_setting', function (Blueprint $table) {
            $table->dropUnique(['user_no']);
            $table->index(['user_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // index削除　ユニークキー追加
        Schema::table('t_user_reserve_setting', function (Blueprint $table) {
            $table->dropIndex(['user_no']);
            $table->unique(['user_no']);
        });
    }
};
