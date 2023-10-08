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
        // スケジュールマスタ
        Schema::create('m_schedule', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('is_weekdays')->default(0)->comment('平日フラグ');
            $table->time('start_time')->comment('開始時間');
            $table->time('end_time')->comment('終了時間');
            $table->boolean('is_lesson')->default(1)->comment('レッスン有/無');
            $table->tinyInteger('time_division_id')->comment('時間区分ID');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes(); // 論理削除
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('m_schedule');
    }
};
