<?php

namespace App\Services\Admin;

use App\Dao\ReserveDao;
use App\Dao\ScheduleDao;
use App\Dao\MemberDao;
use Illuminate\Support\Facades\Log;
use Carbon\CarbonImmutable;

/**
 * カレンダーAPI　（管理）
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
        ,MemberDao $memberDao
    ) {
        $this->reserveDao = $reserveDao;
        $this->scheduleDao = $scheduleDao;
        $this->memberDao = $memberDao;
    }


    /**
     * 予約情報取得
     *
     * @param string $startDate
     * @param string $endDate
     * @return array|null
     */
    public function fetchReserve(string $startDate, string $endDate): ?array
    {
        $result = [];

        $reserveInfo = $this->reserveDao->fetchReserve($startDate, $endDate);
        if ($reserveInfo->isEmpty()) {
            return $result;
        }
        // データ整形
        foreach($reserveInfo->all() as $key => $value) {

            $title = str_pad($value->user_no, 3, '0', STR_PAD_LEFT) . ' ' . ($value->gest_name ? $value->gest_name : $value->name);
            $startTime = $value->start_time;
            $endTime = $value->start_time;
            switch($value->room_id){
                case 1: // room1の場合は0～15分まで
                    $endTime = str_replace('00:00', self::CALENDAR_END_TIME[$value->room_id], $endTime);
                    break;
                case 2: // room1の場合は15～30分まで
                    $startTime = str_replace('00:00', self::CALENDAR_START_TIME[$value->room_id], $startTime);
                    $endTime = str_replace('00:00', self::CALENDAR_END_TIME[$value->room_id], $endTime);
                    break;
                case 3: // room1の場合は30～45分まで
                    $startTime = str_replace('00:00', self::CALENDAR_START_TIME[$value->room_id], $startTime);
                    $endTime = str_replace('00:00', self::CALENDAR_END_TIME[$value->room_id], $endTime);
                    break;
                case 4: // room4の場合は45～59分まで
                    $startTime = str_replace('00:00', self::CALENDAR_START_TIME[$value->room_id], $startTime);
                    $endTime = str_replace('00:00', self::CALENDAR_END_TIME[$value->room_id], $endTime);
                    break;
                default:
            }
            $startDateTime = new CarbonImmutable($value->use_date . ' ' . $startTime);
            $endDateTime = new CarbonImmutable($value->use_date . ' ' . $endTime);

            $result[] = ['title'=> $title
                , 'start'=>$startDateTime->format('c')
                , 'end'=>$endDateTime->format('c')
                , 'user_no'=>$value->user_no
                , 'reserve_id'=>$value->id
            ];
        }


        return $result;
    }

    /**
     * 営業スケジュール取得
     *
     * @param string $startDate
     * @param string $endDate
     * @return array|null
     */
    public function getBusinessSchedule(string $startDate, string $endDate): ?array
    {
        $result = [];

        // スケジュール取得
        $scheduleInfo = $this->scheduleDao->fetchSchedule($startDate, $endDate);
        if ($scheduleInfo->isEmpty()) {
            // スケジュール設定なし
            return $result;
        }
        Log::debug(print_r($scheduleInfo->all(), true));

        // スケジュール整形
        foreach($scheduleInfo->all() as $key => $value) {

            $startDateTime = new CarbonImmutable($value->use_date . ' ' . $value->start_time);
            $endTime = str_replace('50:00', '59:59', $value->end_time);
            $endDateTime = new CarbonImmutable($value->use_date . ' ' . $endTime);

            $result[] = [
                'start'=>$startDateTime->format('c')
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
                $result[] = ['title'=> '休業日'
                    , 'start'=>$startDateTime->format('c')
                    , 'end'=>$endDateTime->format('c')
                    , 'display'=>'background'
                    , 'color'=>'#808080'];
            }
        }
        Log::debug(print_r($result, true));

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
        $tagetDate = $tagetDate->format('Y-m-d');

        $result = $this->memberDao->fetchMemberForAdminReserve($userNo, $tagetDate);

        if (!isset($result)) {
            // 対象会員Noなし
            throw new \Exception('会員が存在しません。(No.' . $userNo . ')', 422);
        }

        return (array)$result;
    }

    /**
     * スケジュール取得
     *
     * @param string $startDate
     * @return bool
     */
    public function fetchSchedule(string $startDate): array
    {
        $result = [];
        $tmpDate = new CarbonImmutable($startDate);
        $tagetDate = $tmpDate->format('Y-m-d');
        $tagetTime = $tmpDate->format('H') . ':00:00';

        $result = $this->scheduleDao->fetchScheduleForAdminReserve($tagetDate, $tagetTime);

        if (!isset($result)) {
            // スケジュールなし(adminは許容する)
            // throw new \Exception('スケジュールが存在しません。(' . $tagetDate . ' ' . $tagetTime . '～)', 422);
            $result = ['id' => 0];
        }

        return (array)$result;
    }

    /**
     * 予約登録
     *
     * @param string $startDate
     * @return int
     */
    public function registRserve(array $scheduleInfo, array $memberInfo, string $startDate): int
    {
        $result = 0;
        $tmpDate = new CarbonImmutable($startDate);
        $tagetDate = $tmpDate->format('Y-m-d');
        $tagetTime = $tmpDate->format('H') . ':00:00';
        $tagetTimeForRoomId = $tmpDate->format('i:s');

        $roomId = 0;
        foreach (self::CALENDAR_START_TIME as  $key=>$value) {
            if ($tagetTimeForRoomId == $value) {
                $roomId = $key;
                break;
            }
        }
        if ($roomId == 0) {            
            throw new \Exception('打席指定エラー(' . $tagetDate . ' ' . $tagetTime . '～)', 422);
        }
        // 予約チェック
        $reserveInfo = $this->reserveDao->fetchReserveForAdminReserve($roomId, $tagetDate, $tagetTime);
        if (!$reserveInfo->isEmpty()) {
            // 既に予約データあり
            throw new \Exception('予約済みエラー(' . $tagetDate . ' ' . $tagetTime . '～ ' . $roomId . '番打席)', 422);
        }

        // 予約情報作成
        $insertData = [
                        't_schedule_id' => $scheduleInfo['id'],
                        'use_date' => $tagetDate,
                        'start_time' => $tagetTime,
                        'room_id' => $roomId,
                        'user_no' => $memberInfo['user_no'],
                        'status' => 1,
                        'update_no' => 'admin_01' // @@@管理者固定値
                    ];
        if ($memberInfo['user_no'] == 'g') {
            // ゲストの場合
            $insertData['gest_name'] = $memberInfo['name'];
        }
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
