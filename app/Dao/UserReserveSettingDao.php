<?php

namespace App\Dao;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use stdClass;

/**
 * ユーザ予約設定
 */
class UserReserveSettingDao
{

    /**
     * ユーザ設定登録
     *
     * @param int $scheduleId
     * @param int $roomId
     * @return int
     */
    public function registUserSetting(array $insertData): int
    {
        return DB::table('t_user_reserve_setting')
            ->insertGetId($insertData, 'id');
    }

}
