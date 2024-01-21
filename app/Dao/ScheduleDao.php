<?php

namespace App\Dao;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use stdClass;

/**
 * スケジュール
 */
class ScheduleDao
{

    /**
     * スケジュール取得
     *
     * @param string $startDate
     * @param string $endDate
     * @param int planId
     * @return Collection
     */
    public function fetchSchedule(string $startDate, string $endDate, int $planId = 0): Collection
    {
        $date = Carbon::now();

        $arrTimeDivision = config('const.time_division');
        $timeDivision = $arrTimeDivision[$planId];

        $query = DB::table('t_schedule AS ts')
            ->join('m_schedule AS ms', 'ts.m_schedule_id', '=', 'ms.id')
            ->select(
                'ts.id'
                ,'ts.use_date'
                ,'ms.start_time'
                ,'ms.end_time'
                ,'ms.is_weekdays'
                ,'ts.is_lesson'
            )
            ->where('ts.use_date', '>=', $startDate)
            ->where('ts.use_date', '<=', $endDate)
            ->whereNull('ts.deleted_at')
            ->whereNull('ms.deleted_at')
//            ->where('ts.use_date', '>=', $date->format('Y-m-d'))
            ->orderBy('ts.use_date', 'asc')
            ->orderBy('ms.start_time', 'asc');

        if (!empty($timeDivision)) {
            // 時間区分設定
            $query->whereIn('ms.time_division_id', $timeDivision);
        }

        return $query->get() ?? collect([]);
    }

    /**
     * スケジュール取得 ユーザー予約用
     *
     * @param string $targetDate
     * @param string $targetTime
     * @return stdClass|null
     */
    public function fetchScheduleForReserve(string $targetDate, string $targetTime, int $planId = 0): ?stdClass
    {
        $arrTimeDivision = config('const.time_division');
        $timeDivision = $arrTimeDivision[$planId];

        return DB::table('t_schedule AS ts')
            ->join('m_schedule AS ms', 'ts.m_schedule_id', '=', 'ms.id')
            ->select(
                'ts.id'
                ,'ts.use_date'
                ,'ms.start_time'
                ,'ms.end_time'
                ,'ts.is_lesson'
            )
            ->where('ts.use_date', '=', $targetDate)
            ->where('ms.start_time', '=', $targetTime)
            ->whereIn('ms.time_division_id', $timeDivision)
            ->whereNull('ms.deleted_at')
            ->whereNull('ts.deleted_at')
            ->first();
    }

    /**
     * スケジュール取得 管理者予約用
     *
     * @param string $targetDate
     * @param string $targetTime
     * @return stdClass|null
     */
    public function fetchScheduleForAdminReserve(string $targetDate, string $targetTime): ?stdClass
    {
        $date = Carbon::now();

        return DB::table('t_schedule AS ts')
            ->join('m_schedule AS ms', 'ts.m_schedule_id', '=', 'ms.id')
            ->select(
                'ts.id'
                ,'ts.use_date'
                ,'ms.start_time'
                ,'ms.end_time'
                ,'ts.is_lesson'
            )
            ->where('ts.use_date', '=', $targetDate)
            ->where('ms.start_time', '=', $targetTime)
            ->whereNull('ms.deleted_at')
            ->whereNull('ts.deleted_at')
            ->first();
    }

    /**
     * マスタスケジュール取得 管理者用
     *
     * @return Collection
     */
    public function fetchMasterSchedule(): Collection
    {

        return DB::table('m_schedule AS ms')
            ->select(
                'ms.id'
                ,'ms.is_weekdays'
                ,'ms.start_time'
                ,'ms.end_time'
                ,'ms.is_lesson'
                ,'ms.time_division_id'
            )
            ->whereNull('ms.deleted_at')
            ->get() ?? collect([]);;
    }

    /**
     * マスタスケジュール取得 管理者用
     *
     * @param array $target
     * @return stdClass
     */
    public function fetchMasterScheduleForId(array $target): ?stdClass
    {

        return DB::table('m_schedule AS ms')
            ->select(
                'ms.id'
            )
            ->where('id', $target['m_schedule_id'])
            ->whereNull('ms.deleted_at')
            ->first();
    }

    /**
     * スケジュール登録（管理者用）
     *
     * @param array $insertData
     * @return int
     */
    public function registSchedule(array $insertData): int
    {
        return DB::table('t_schedule')
            ->insertGetId($insertData, 'id');
    }

    /**
     * スケジュール削除（管理者用）
     *
     * @param array $target
     * @return int
     */
    public function deleteSchedule(array $target): int
    {
        return DB::table('t_schedule')
            ->where('id', $target['schedule_id'])
            ->whereNull('deleted_at')
            ->delete();
    }
}
