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
        // ユーザー予約設定
        Schema::create('t_user_reserve_setting', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_no', 20)->default('---')->comment('会員NO');
            $table->tinyInteger('plan_id')->default(0)->comment('プランID');
            $table->timestamp('expire_start')->useCurrent()->comment('有効期限開始日');
            $table->timestamp('expire_end')->nullable()->comment('有効期限終了日');
            $table->timestamp('reserve_start')->useCurrent()->comment('予約開始日');
            $table->timestamp('reserve_end')->nullable()->comment('予約終了日');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes(); // 論理削除

            // unique
            $table->unique(['user_no']);
            // index
            $table->index(['expire_start', 'expire_end'], 'idx_expire_start_end');
            $table->index(['reserve_start', 'reserve_end'], 'idx_reserve_start_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_user_reserve_setting');
    }
};
