<?php

namespace App\Dao;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use stdClass;

/**
 * 会員
 */
class MemberDao
{

    /**
     * 会員取得 管理者予約用
     *
     * @param string $userNo
     * @param string $targetDate
     * @return stdClass|null
     */
    public function fetchMemberForAdminReserve(string $userNo, string $targetDate): ?stdClass
    {

        $start = $targetDate . ' 00:00:00';
        $end = $targetDate . ' 59:59:59';

        return DB::table('t_member AS tm')
            ->select(
                'tm.user_no'
                ,'tm.name'
            )
            ->where('tm.user_no', '=', $userNo)
            ->where('tm.expire_start', '<=', $start)
            ->where(function($query) use($end) {
                $query->whereNull('tm.expire_end')
                    ->orWhere('tm.expire_end', '=>', $end);
            })
            ->where('tm.reserve_start', '<=', $start)
            ->where(function($query) use($end) {
                $query->whereNull('tm.reserve_end')
                    ->orWhere('tm.reserve_end', '=>', $end);
            })
            ->whereNull('deleted_at')
            ->first();
    }
}
