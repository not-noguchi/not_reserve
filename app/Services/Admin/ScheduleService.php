<?php

namespace App\Services\Admin;

use App\Dao\ReserveDao;
use App\Dao\ScheduleDao;
use App\Dao\UserDao;
use Illuminate\Support\Facades\Log;
use Carbon\CarbonImmutable;

/**
 * スケジュール設定　（管理）
 */
class ScheduleService
{
    /** @var ReserveDao 予約 */
    private $reserveDao;
    /** @var ScheduleDao スケジュール */
    private $scheduleDao;
    /** @var UserDao ユーザー */
    private $userDao;

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
     * @return array|null
     */
    public function fetchReserve(string $startDate, string $endDate): ?array
    {
        $result = [];

        $reserveInfo = $this->reserveDao->fetchReserveForAdmin($startDate, $endDate);
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

            $color = '#3788d8';
            if ($value->user_no == 'g') {
                // gestの場合
                $color = '#ff69b4';
            }
            $result[] = ['title'=> $title
                , 'start'=>$startDateTime->format('c')
                , 'end'=>$endDateTime->format('c')
                , 'user_no'=>$value->user_no
                , 'reserve_id'=>$value->id
                , 'color'=>$color
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

        // スケジュール整形
        foreach($scheduleInfo->all() as $key => $value) {

            $startDateTime = new CarbonImmutable($value->use_date . ' ' . $value->start_time);
            $endTime = str_replace('50:00', '59:59', $value->end_time);
            $endDateTime = new CarbonImmutable($value->use_date . ' ' . $endTime);

            // 休日カラー
            $title = '休日:';
            $color = '#3cb371';
            if ($value->is_weekdays) {
                // 平日カラー
                $color = '#ff8c00';
                $title = '平日:';
            }
            $title .= substr($value->start_time, 0 ,2) . '～';
            
            $result[] = [
                'title'=> $title
                , 'start'=>$startDateTime->format('c')
                , 'end'=>$endDateTime->format('c')
//                , 'display'=>'background'
                , 'color'=>$color
                , 'schedule_id'=>$value->id
            ];
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

        $result = $this->userDao->fetchUserForAdminReserve($userNo, $tagetDate);

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
     * @return array
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
    public function registSchedule(array $request): int
    {
        // マスタスケジュールチェック
        $scheduleInfo = $this->scheduleDao->fetchMasterScheduleForId($request);
        if (is_null($scheduleInfo)) {
            // データなし
            throw new \Exception('マスタスケジュールエラー(ID:' . $request['m_schedule_id'] . ')', 422);
        }

        // スケジュール情報取得
        $useDate = date('Y-m-d', $request['use_date'] / 1000);

        // スケジュール情報作成
        $insertData = [
                        'm_schedule_id' => $scheduleInfo->id,
                        'use_date' => $useDate,
                    ];
        // スケジュール登録
        $result = $this->scheduleDao->registSchedule($insertData);

        return $result;
    }

    /**
     * スケジュール削除(0件更新の場合、falseを返却)
     *
     * @param array $request
     * @return bool
     */
    public function deleteSchedule(array $request): bool
    {
        // スケジュール削除
        return $this->scheduleDao->deleteSchedule($request);
    }

    /**
     * マスタスケジュール取得
     *
     * @return array
     */
    public function fetchMasterSchedule(): array
    {
        $masterSchedule = [];
        // マスタスケジュール取得
        $tmpMasterSchedule = $this->scheduleDao->fetchMasterSchedule();
        // 整形
        foreach($tmpMasterSchedule->all() as $key => $value) {
            $masterSchedule[$value->is_weekdays][str_replace(':00:00', '', $value->start_time)] = [
                    'id' => $value->id
                    ,'start_time' => $value->start_time
                    ,'end_time' => $value->end_time
                    ,'is_lesson' => $value->is_lesson
                    ,'time_division_id' => $value->time_division_id
                ];
        }

        return $masterSchedule;
    }

}
