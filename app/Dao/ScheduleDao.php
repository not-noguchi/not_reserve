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
                'ts.use_date'
                ,'ms.start_time'
                ,'ms.end_time'
                ,'ts.is_lesson'
            )
            ->where('ts.use_date', '>=', $startDate)
            ->where('ts.use_date', '<=', $endDate)
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
            ->first();
    }
}
