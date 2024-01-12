<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_user', function (Blueprint $table) {
            $table->string('api_token', 80)->after('password')->unique()->nullable()->default(null);
        });
        // 既存登録ユーザーにapi_tokenを登録
        User::all()->each (function(User $user) {
            $user->update(['api_token' => Str::random(60)]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_user', function (Blueprint $table) {
            $table->dropColumn('api_token');
        });
    }
};
