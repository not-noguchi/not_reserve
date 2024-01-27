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
class UserDao
{

    /**
     * 会員取得
     *
     * @param string $userNo
     * @param string $targetDate
     * @return stdClass|null
     */
    public function fetchUser(string $userNo, string $targetDate): ?stdClass
    {
        return DB::table('t_user AS tu')
            ->join('t_user_reserve_setting AS turs', 'tu.user_no', '=', 'turs.user_no')
            ->select(
                'tu.user_no'
                ,'tu.name'
                ,'turs.plan_id'
            )
            ->where('tu.user_no', '=', $userNo)
            ->where('turs.expire_start', '<=', $targetDate)
            ->where(function($query) use($targetDate) {
                $query->whereNull('turs.expire_end')
                    ->orWhere('turs.expire_end', '>=', $targetDate);
            })
            ->where('turs.reserve_start', '<=', $targetDate)
            ->where(function($query) use($targetDate) {
                $query->whereNull('turs.reserve_end')
                    ->orWhere('turs.reserve_end', '>=', $targetDate);
            })
            ->whereNull('tu.deleted_at')
            ->whereNull('turs.deleted_at')
            ->first();
    }

    /**
     * 会員取得 管理者予約用
     *
     * @param string $userNo
     * @param string $targetDate
     * @return stdClass|null
     */
    public function fetchUserForAdminReserve(string $userNo, string $targetDate): ?stdClass
    {

        $start = $targetDate . ' 00:00:00';
        $end = $targetDate . ' 59:59:59';

        return DB::table('t_user AS tu')
            ->join('t_user_reserve_setting AS turs', 'tu.user_no', '=', 'turs.user_no')
            ->select(
                'tu.user_no'
                ,'tu.name'
            )
            ->where('tu.user_no', '=', $userNo)
            ->where('turs.expire_start', '<=', $start)
            ->where(function($query) use($end) {
                $query->whereNull('turs.expire_end')
                    ->orWhere('turs.expire_end', '>=', $end);
            })
            ->where('turs.reserve_start', '<=', $start)
            ->where(function($query) use($end) {
                $query->whereNull('turs.reserve_end')
                    ->orWhere('turs.reserve_end', '>=', $end);
            })
            ->whereNull('tu.deleted_at')
            ->whereNull('turs.deleted_at')
            ->first();
    }

    /**
     * 会員一覧取得
     *
     * @return Collection
     */
    public function fetchUserListForAdmin(): Collection
    {
        $date = Carbon::now();

        return DB::table('t_user AS tu')
            ->join('t_user_reserve_setting AS turs', 'tu.user_no', '=', 'turs.user_no')
            ->select(
                'tu.id'
                ,'tu.user_no'
                ,'tu.name'
                ,'tu.nic_name'
                ,'tu.email'
                ,'tu.created_at'
                ,'turs.plan_id'
                ,'turs.expire_start'
                ,'turs.expire_end'
                ,'turs.reserve_start'
                ,'turs.reserve_end'
            )
            ->where('turs.expire_start', '<=', $date)
            ->where(function($query) use($date) {
                $query->whereNull('turs.expire_end')
                    ->orWhere('turs.expire_end', '>=', $date);
            })
            ->whereNull('tu.deleted_at')
            ->whereNull('turs.deleted_at')
            ->where('tu.user_no', 'NOT LIKE', 'test%')
            ->orderByRaw('cast(tu.user_no as SIGNED) ASC')
            ->orderBy('turs.reserve_start', 'asc')
            ->get() ?? collect([]);;
    }

    /**
     * ユーザ削除
     *
     * @param int $userNo
     * @return int
     */
    public function deleteUser(int $userId): int
    {
        $user = DB::table('t_user AS tu')
            ->select(
                'tu.user_no'
                ,'tu.email'
            )
            ->where('tu.id', '=', $userId)
            ->whereNull('tu.deleted_at')
            ->first();

        $date = Carbon::now();
        DB::table('t_user_reserve_setting')
            ->where('user_no', $user->user_no)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => $date]);

        return DB::table('t_user')
            ->where('id', $userId)
            ->whereNull('deleted_at')
            ->update([
                'user_no' => $user->user_no . '_' . $userId
                ,'email' => $user->email . '_' . $userId
                ,'deleted_at' => $date]);
    }
}
