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
        Schema::create('t_schedule', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('m_schedule_id')->comment('スケジュールマスタID');
            $table->date('use_date')->comment('利用日');
            $table->boolean('is_lesson')->default(1)->comment('レッスン有/無');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes(); // 論理削除

            // index
            $table->unique(['m_schedule_id', 'use_date'], 'uidx_schedule_id_use_date');
            $table->index(['use_date'], 'idx_use_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('t_schedule');
    }
};
