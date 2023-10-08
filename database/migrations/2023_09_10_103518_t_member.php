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
        Schema::create('t_member', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id', 20)->comment('会員ID');
            $table->string('user_no', 20)->comment('会員NO');
            $table->string('name', 50)->comment('氏名');
            $table->string('nic_name', 50)->nullable()->comment('ニックネーム');
            $table->string('mail', 50)->nullable()->comment('メールアドレス');
            $table->string('password', 128)->comment('パスワード');
            $table->tinyInteger('plan_id')->comment('プランID');
            $table->boolean('is_left')->default(0)->comment('左打席');
            $table->string('address', 128)->nullable()->comment('住所');
            $table->string('tel', 11)->nullable()->comment('電話番号');
            $table->tinyInteger('gender')->nullable()->comment('性別　1:男性、2:女性');
            $table->date('join_date')->comment('入会日');
            $table->string('join_medium', 20)->nullable()->comment('入会媒体');
            $table->timestamp('expire_start')->useCurrent()->comment('有効期限開始日');
            $table->timestamp('expire_end')->nullable()->comment('有効期限終了日');
            $table->timestamp('reserve_start')->useCurrent()->comment('予約開始日');
            $table->timestamp('reserve_end')->nullable()->comment('予約終了日');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes(); // 論理削除

            // index
            $table->unique(['user_id'], 'uidx_user_id');
            $table->index(['user_no'], 'idx_user_no');
            $table->index(['expire_start', 'expire_end'], 'idx_expire_start_end');
            $table->index(['reserve_start', 'reserve_end'], 'idx_reserve_start_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('t_member');
    }
};
