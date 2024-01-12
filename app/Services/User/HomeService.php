<?php

namespace App\Services\User;

use App\Dao\ReserveDao;
use App\Dao\ScheduleDao;
use App\Dao\UserDao;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

/**
 * ホーム画面サービスクラス
 */
class HomeService
{
    /** @var ReserveDao 予約 */
    private $reserveDao;
    /** @var ScheduleDao スケジュール */
    private $scheduleDao;
    /** @var UserDao ユーザー */
    private $userDao;

    /**
     * コンストラクタ
     *
     * @param ReserveDao $reserveDao
     */
    public function __construct(
        ReserveDao $reserveDao
        ,ScheduleDao $scheduleDao
        ,UserDao $userDao
    ) {
        $this->reserveDao = $reserveDao;
        $this->scheduleDao = $scheduleDao;
        $this->userDao = $userDao;
    }


    /**
     * 予約情報取得
     *
     * @param string $userNo
     * @return array|null
     */
    public function fetchReserve(string $userNo): ?array
    {
        $result = [];
        $tagetDate = Carbon::now();
        $tagetDate = $tagetDate->format('Y-m-d');

        $reserveInfo = $this->reserveDao->fetchReserveForHome($tagetDate, $userNo);
        if (!$reserveInfo->isEmpty()) {
            $result = $reserveInfo->all();
        }

        return $result;
    }


    /**
     * 会員情報取得
     *
     * @param string $userNo
     * @return bool
     */
    public function fetchMember(string $userNo): array
    {
        $result = [];
        $tagetDate = Carbon::now();
        $tagetDate = $tagetDate->format('Y-m-d H:i:s');

        $result = $this->userDao->fetchUser($userNo, $tagetDate);

        if (!isset($result)) {
            // 対象会員Noなし
            throw new \Exception('会員が存在しません。(No.' . $userNo . ')', 422);
        }

        return (array)$result;
    }

    /**
     * 予約件数取得
     *
     * @param array $userInfo
     * @return array
     */
    public function fetchReserveCnt(array $userInfo): int
    {
        $result = 0;
        $result = $this->reserveDao->fetchReserveForCheck($userInfo['user_no']);

        return $result;
    }
}
