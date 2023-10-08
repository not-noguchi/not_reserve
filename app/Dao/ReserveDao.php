<?php

namespace App\Dao;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use stdClass;

/**
 * 予約
 */
class ReserveDao
{

    /**
     * 予約情報取得
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function fetchReserve(string $startDate, string $endDate): Collection
    {
        return DB::table('t_reserve AS tr')
            ->join('t_member AS tm', 'tr.user_no', '=', 'tm.user_no')
            // ->join('t_schedule AS ts', 'tr.t_schedule_id', '=', 'ts.id')
            // ->join('m_schedule AS ms', 'ts.m_schedule_id', '=', 'ms.id')
            ->select(
                'tm.user_no'
                ,'tm.name'
                ,'tr.use_date'
                ,'tr.start_time'
                ,'tr.room_id'
                ,'tr.gest_name'
                ,'tr.id'
            )
            ->where('tr.use_date', '>=', $startDate)
            ->where('tr.use_date', '<=', $endDate)
            ->whereIn( 'tr.status', [1, 2] )
            ->orderBy('tr.use_date', 'asc')
            ->orderBy('tr.start_time', 'asc')
            ->get() ?? collect([]);
    }

    /**
     * 予約情報取得 管理者予約用
     *
     * @param string $tagetDate
     * @param string $tagetTime
     * @param int $roomId
     * @return Collection
     */
    public function fetchReserveForAdminReserve(int $roomId, string $tagetDate, string $tagetTime): Collection
    {
        return DB::table('t_reserve AS tr')
            ->select(
                'tr.id'
            )
            ->where('tr.use_date', '=', $tagetDate)
            ->where('tr.start_time', '=', $tagetTime)
            ->where('tr.room_id', '=', $roomId)
            ->whereIn( 'tr.status', [1, 2] )
            ->get() ?? collect([]);
    }

    /**
     * 予約登録
     *
     * @param int $scheduleId
     * @param int $roomId
     * @return int
     */
    public function registReserve(array $insertData): int
    {
        return DB::table('t_reserve')
            ->insertGetId($insertData, 'id');
    }


    /**
     * 予約キャンセル
     *
     * @param array $request
     * @return int
     */
    public function cancelReserve(array $request): int
    {
        return DB::table('t_reserve')
            ->where('id', $request['reserve_id'])
            ->where('user_no', $request['user_no'])
            ->update(['status' => 3]);
    }

}
