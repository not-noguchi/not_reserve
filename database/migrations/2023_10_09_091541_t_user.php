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
        // ユーザー
        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_no', 20)->comment('会員NO');
            $table->string('name')->comment('氏名');
            $table->string('nic_name', 50)->nullable()->comment('ニックネーム');
            $table->string('email')->unique()->comment('メールアドレス');
            $table->string('tel', 15)->nullable()->comment('電話番号(通常max11桁だが流用性を持たせ15桁)');
            $table->string('address', 128)->nullable()->comment('住所');
            $table->tinyInteger('gender')->nullable()->comment('性別　1:男性、2:女性');
            $table->date('date_of_birth')->nullable()->comment('生年月日');
            $table->boolean('is_left')->default(0)->comment('左打席');
            $table->date('join_date')->nullable()->comment('入会日');
            $table->string('join_medium', 20)->nullable()->comment('入会媒体');
            $table->timestamp('email_verified_at')->nullable()->comment('メールアドレス承認日時');
            $table->string('password')->comment('パスワード');
            $table->tinyInteger('is_force_logout')->default(0)->comment('強制ログアウト');
            $table->rememberToken()->comment('パスワードリセット用トークン');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes(); // 論理削除
            // unique
            $table->unique(['user_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_user');
    }
};
