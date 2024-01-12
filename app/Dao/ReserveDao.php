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
    public function fetchReserve(string $startDate, string $endDate, string $userNo): Collection
    {
        return DB::table('t_reserve AS tr')
            ->join('t_user AS tu', 'tr.user_no', '=', 'tu.user_no')
            ->select(
                'tu.user_no'
                ,'tu.name'
                ,'tr.use_date'
                ,'tr.start_time'
                ,'tr.room_id'
                ,'tr.gest_name'
                ,'tr.id'
            )
//            ->where('tu.user_no', $userNo)
            ->where('tr.use_date', '>=', $startDate)
            ->where('tr.use_date', '<=', $endDate)
            ->whereIn( 'tr.status', [1, 2] )
            ->orderBy('tr.use_date', 'asc')
            ->orderBy('tr.start_time', 'asc')
            ->get() ?? collect([]);
    }

    /**
     * 予約情報取得（ホーム画面用）
     *
     * @param string $startDate
     * @return Collection
     */
    public function fetchReserveForHome(string $startDate, string $userNo): Collection
    {
        return DB::table('t_reserve AS tr')
            ->join('t_user AS tu', 'tr.user_no', '=', 'tu.user_no')
            ->select(
                'tu.user_no'
                ,'tu.name'
                ,'tr.use_date'
                ,'tr.start_time'
                ,'tr.id'
            )
            ->where('tu.user_no', $userNo)
            ->where('tr.use_date', '>=', $startDate)
            ->whereIn( 'tr.status', [1, 2] )
            ->orderBy('tr.use_date', 'asc')
            ->orderBy('tr.start_time', 'asc')
            ->get() ?? collect([]);
    }


    /**
     * 予約情報取得(予約チェック用)
     *
     * @param string $userNo
     * @return int
     */
    public function fetchReserveForCheck(string $userNo): int
    {
        $date = Carbon::now();

        return DB::table('t_reserve AS tr')
            ->join('t_user AS tu', 'tr.user_no', '=', 'tu.user_no')
            ->select(
                'tu.user_no'
            )
            ->where('tu.user_no', $userNo)
//            ->where('tr.use_date', '>=', $date->format('Y-m-d'))
            ->where(DB::raw('CONCAT(tr.use_date,' . "' '" . ', tr.start_time)'), '>=', $date->format('Y-m-d H:i:s'))
            ->whereIn( 'tr.status', [1, 2] )
            ->count();
    }

    /**
     * 予約情報取得 管理者用
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function fetchReserveForAdmin(string $startDate, string $endDate): Collection
    {
        return DB::table('t_reserve AS tr')
//            ->join('t_user_reserve_setting AS tus', 'tr.user_no', '=', 'tus.user_no')
            ->leftJoin('t_user AS tu', 'tr.user_no', '=', 'tu.user_no')
            // ->join('t_schedule AS ts', 'tr.t_schedule_id', '=', 'ts.id')
            // ->join('m_schedule AS ms', 'ts.m_schedule_id', '=', 'ms.id')
            ->select(
                'tu.user_no'
                ,'tu.name'
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
     * 予約情報取得 ユーザー予約用
     *
     * @param string $tagetDate
     * @param string $tagetTime
     * @param int $roomId
     * @return Collection
     */
    public function fetchReserveForReserve(string $tagetDate, string $tagetTime): Collection
    {
        return DB::table('t_reserve AS tr')
            ->select(
                'tr.id'
                ,'tr.room_id'
            )
            ->where('tr.use_date', '=', $tagetDate)
            ->where('tr.start_time', '=', $tagetTime)
            ->whereIn( 'tr.status', [1, 2] )
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
