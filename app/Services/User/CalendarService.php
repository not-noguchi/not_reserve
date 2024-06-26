<?php

namespace App\Services\User;

use App\Dao\ReserveDao;
use App\Dao\ScheduleDao;
use App\Dao\UserDao;
use Illuminate\Support\Facades\Log;
use Carbon\CarbonImmutable;
use Carbon\Carbon;

/**
 * カレンダーAPI　（ユーザー）
 */
class CalendarService
{
    /** @var ReserveDao 予約 */
    private $reserveDao;
    /** @var ScheduleDao スケジュール */
    private $scheduleDao;

    private const CALENDAR_START_TIME = [
        1 => '00:00'
        , 2 => '15:00'
        , 3 => '30:00'
        , 4 => '45:00'];

    private const CALENDAR_END_TIME = [
        1 => '15:00'
        , 2 => '30:00'
        , 3 => '45:00'
        , 4 => '59:59'];

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
     * @param string $startDate
     * @param string $endDate
     * @param string $userNo
     * @return array|null
     */
    public function fetchReserve(string $startDate, string $endDate, string $userNo): ?array
    {
        $result = [];

        $reserveInfo = $this->reserveDao->fetchReserve($startDate, $endDate, $userNo);
        if ($reserveInfo->isEmpty()) {
            return $result;
        }
        // データ整形
        foreach($reserveInfo->all() as $key => $value) {

            $title = $value->user_no;
            $startTime = $value->start_time;
            $endTime = $value->start_time;
            $startDateTime = new CarbonImmutable($value->use_date . ' ' . $startTime);
            $endDateTime = new CarbonImmutable($value->use_date . ' ' . $endTime);
            $is_self = false;
            if ($value->user_no == $userNo) {
                $is_self = true;
            }


            if (!isset($result[$value->use_date][$startTime])) {
                $result[$value->use_date][$startTime] = ['title'=> $title
                    , 'start' => $startDateTime->format('c')
                    , 'end' => $endDateTime->format('c')
                    , 'user_no' => $value->user_no
                    , 'reserve_id' => $value->id
                    , 'is_self' => $is_self
                    , 'reserve_cnt' => 1
                ];
            } else if ($is_self) {
                $result[$value->use_date][$startTime]['is_self'] = $is_self;
                $result[$value->use_date][$startTime]['reserve_id'] = $value->id;
                $result[$value->use_date][$startTime]['reserve_cnt'] += 1;
            } else {
                $result[$value->use_date][$startTime]['reserve_cnt'] += 1;
            }
        }
        return $result;
    }

    /**
     * 営業スケジュール取得
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $userNo
     * @return array|null
     */
    public function getBusinessSchedule(string $startDate, string $endDate, string $userNo): ?array
    {
        $result = [];

        $tagetDate = Carbon::now();
        $userInfo = $this->userDao->fetchUser($userNo, $tagetDate->format('Y-m-d H:i:s'));
        $nowDate = $tagetDate->format('Y-m-d');
        $startScheduleDate = $startDate;
        if ($nowDate > $startDate) {
            // 開始日変更
            $startScheduleDate = $nowDate;
        }
        // スケジュール取得
        $scheduleInfo = $this->scheduleDao->fetchSchedule($startScheduleDate, $endDate, $userInfo->plan_id);
        if ($scheduleInfo->isEmpty()) {
            // スケジュール設定なし
            return $result;
        }
        // スケジュール整形
        foreach($scheduleInfo->all() as $key => $value) {

            $startDateTime = new CarbonImmutable($value->use_date . ' ' . $value->start_time);
            $endTime = str_replace('50:00', '59:59', $value->end_time);
            $endDateTime = new CarbonImmutable($value->use_date . ' ' . $endTime);

            $result['business'][$value->use_date][$value->start_time] = [
                'startDisp' => substr($value->start_time, 0, 5)

                ,'start'=>$startDateTime->format('c')
                , 'end'=>$endDateTime->format('c')
                , 'display'=>'background'
                , 'color'=>'#ffa500'];
        }

        // スケジュールにない日付を休業日に設定
        for ($date = $startDate; $date <= $value->use_date; $date = date('Y-m-d', strtotime($date . '+1 day'))) {

            $filtered = $scheduleInfo->where('use_date', $date);
            if ($filtered->isEmpty()) {
                $startDateTime = new CarbonImmutable($date . ' ' . '00:00:00');
                $endDateTime = new CarbonImmutable($date . ' ' . '23:59:59');
                $result['closed'][] = ['title'=> '休業日'
                    , 'start'=>$startDateTime->format('c')
                    , 'end'=>null
                    , 'display'=>'background'
                    , 'color'=>'#808080'
                    , 'date'=> $date
                ];
            }
        }

        return $result;
    }


    /**
     * 会員情報取得
     *
     * @param string $userNo
     * @param string $startDate
     * @return bool
     */
    public function fetchMember(string $userNo, string $startDate): array
    {
        $result = [];
        $tagetDate = new CarbonImmutable($startDate);
        $tagetDate = $tagetDate->format('Y-m-d H:i:s');

        $result = $this->userDao->fetchUser($userNo, $tagetDate);

        if (!isset($result)) {
            // 対象会員Noなし
            throw new \Exception('会員が存在しません。(No.' . $userNo . ')', 422);
        }

        return (array)$result;
    }

    /**
     * 予約チェック
     *
     * @param array $userInfo
     * @return void
     */
    public function fetchReserveForCheck(array $userInfo, CarbonImmutable $startDateCarbon): void
    {
        $tagetDate = $startDateCarbon->format('Y-m-d');
        $tagetTime = $startDateCarbon->format('H') . ':00:00';
        $date = Carbon::now();
        $isNowDate = false;
        if ($tagetDate == $date->format('Y-m-d')) {
            // 当日予約
            $isNowDate = true;
        }

        // 当日予約数
        $todayCnt = 0;
        // 同一日予約開始日
        $sameDayReserveStartTime = [];
        // 未来予約数
        $futureCnt = 0;
        $reserveInfo = $this->reserveDao->fetchReserveForCheck($userInfo['user_no'], $isNowDate);
        if (!$reserveInfo->isEmpty()) {
            foreach($reserveInfo->all() as $reserve) {
                if ($tagetDate == $reserve->use_date && $tagetTime == $reserve->start_time) {
                    // 予約あり(自分)
                    throw new \Exception('同一日時予約済みエラー(' . $tagetDate . ' ' . $tagetTime . '～ )', 422);
                } elseif ($tagetDate == $reserve->use_date) {
                    // 同一日予約開始日の保存
                    $sameDayReserveStartTime[] = $reserve->start_time;
                }
                if ($isNowDate && $tagetDate == $reserve->use_date) {
                    $todayCnt++;
                }
                if ($reserve->use_date . ' ' . $reserve->start_time >= $date->format('Y-m-d H:i:s')) {
                    $futureCnt++;
                }
            }
        }
        $mstReserveCnt = config('const.reserve_cnt');
        if (
            $mstReserveCnt[$userInfo['plan_id']] <= $futureCnt
        ) {
            // 最大予約数オーバー(未来の予約数チェック)
            throw new \Exception('予約登録エラー(予約数オーバー)', 500);
        } elseif (
            $isNowDate
            && $mstReserveCnt[$userInfo['plan_id']] <= $todayCnt
        ) {
            // 当日最大予約数オーバー
            throw new \Exception('予約登録エラー(当日予約数オーバー)', 500);
        }

        if (count($sameDayReserveStartTime) > 0) {
            // 同一日予約ありの場合、連続予約チェック
            $tagetTimeAdd = $startDateCarbon->addHour()->format('H') . ':00:00';
            $tagetTimeSub = $startDateCarbon->subHour()->format('H') . ':00:00';
            $isContinuous = false;
            foreach($sameDayReserveStartTime as $startTime) {
                if ($startTime == $tagetTimeAdd || $startTime == $tagetTimeSub) {
                    // 前後1時間に予約あり
                    $isContinuous = true;
                    break;
                }
            }
            if (!$isContinuous) {
                // 連続予約でない場合、エラー
                throw new \Exception('同日複数ご予約の場合、連続した時間をご指定ください', 500);
            }
        }
    }

    /**
     * スケジュール取得
     *
     * @param CarbonImmutable $startDateCarbon
     * @param array $userInfo
     * @return array
     */
    public function fetchSchedule(CarbonImmutable $startDateCarbon, array $userInfo): array
    {
        $result = [];
        $tagetDate = $startDateCarbon->format('Y-m-d');
        $tagetTime = $startDateCarbon->format('H') . ':00:00';

        $result = $this->scheduleDao->fetchScheduleForAdminReserve($tagetDate, $tagetTime, $userInfo['plan_id']);

        if (!isset($result)) {
            // スケジュールなし(adminは許容する)
            throw new \Exception('スケジュールが存在しません。(' . $tagetDate . ' ' . $tagetTime . '～)', 422);
        }

        return (array)$result;
    }

    /**
     * 予約登録
     *
     * @param CarbonImmutable $startDateCarbon
     * @param array $userInfo
     * @return int
     */
    public function registRserve(array $scheduleInfo, array $userInfo, CarbonImmutable $startDateCarbon): int
    {
        $result = 0;
        $tagetDate = $startDateCarbon->format('Y-m-d');
        $tagetTime = $startDateCarbon->format('H:i:s');
        $arrRoomId = [1=>1, 2=>2, 3=>3, 4=>4];

        // 予約チェック
        $reserveInfo = $this->reserveDao->fetchReserveForReserve($tagetDate, $tagetTime);
        if (!$reserveInfo->isEmpty()) {
            foreach($reserveInfo->all() as $reserve) {
                // 予約済みのroomを削除
                unset($arrRoomId[$reserve->room_id]);
            }
            if (count($arrRoomId) == 0) {
                // 既に予約データあり
                throw new \Exception('予約済みエラー(' . $tagetDate . ' ' . $tagetTime . '～ ', 422);
            }
        }
        $roomId = reset($arrRoomId);

        // 予約情報作成
        $insertData = [
                        't_schedule_id' => $scheduleInfo['id'],
                        'use_date' => $tagetDate,
                        'start_time' => $tagetTime,
                        'room_id' => $roomId,
                        'user_no' => $userInfo['user_no'],
                        'status' => 1,
                        'update_no' => $userInfo['user_no']
                    ];
        // 予約登録
        $result = $this->reserveDao->registReserve($insertData);

        return $result;
    }

    /**
     * 予約キャンセル(0件更新の場合、falseを返却)
     *
     * @param array $request
     * @return bool
     */
    public function cancelReserve(array $request): bool
    {
        // 予約キャンセル
        return $this->reserveDao->cancelReserve($request);
    }

}
