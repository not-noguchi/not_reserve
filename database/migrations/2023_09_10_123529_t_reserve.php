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
        // 予約
        Schema::create('t_reserve', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('t_schedule_id')->comment('スケジュールID');
            $table->date('use_date')->comment('利用日');
            $table->time('start_time')->comment('開始時間');
            $table->tinyInteger('room_id')->comment('打席ID');
            $table->string('user_no')->comment('会員NO');
            $table->string('gest_name', 50)->nullable()->comment('ゲスト氏名');
            $table->tinyInteger('status')->comment('ステータス（予約/受講/キャンセル/欠席）');
            $table->string('update_no', 20)->comment('更新者No');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes(); // 論理削除

            // index
            $table->index(['t_schedule_id'], 'idx_schedule_id');
            $table->index(['use_date'], 'idx_use_date');
            $table->index(['start_time'], 'idx_start_time');
            $table->index(['user_no'], 'idx_member_no');
            $table->index(['status'], 'idx_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('t_reserve');
    }
};
