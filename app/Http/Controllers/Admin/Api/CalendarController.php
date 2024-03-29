<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\CalendarService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\CarbonImmutable;

/**
 * カレンダーAPI(管理)
 */
class CalendarController extends Controller
{
    /** @var CalendarService カレンダーAPIサービスクラス */
    private $service;

    /**
     * コンストラクタ
     *
     * @param CalendarService $service
     */
    public function __construct(CalendarService $service)
    {
        $this->service = $service;
    }

    /**
     * カレンダー情報取得
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fetch(Request $request): JsonResponse
    {
        // バリデーション
        $request->validate([
            'start_date' => 'required|integer',
            'end_date' => 'required|integer'
        ]);
        // カレンダー表示期間取得
        $startDate = date('Y-m-d', $request->input('start_date') / 1000);
        $endDate = date('Y-m-d', $request->input('end_date') / 1000);
        $resultInfo = ['code'=>200, 'message'=>''];

        $reserve = $this->service->fetchReserve($startDate, $endDate);
        $schedule = $this->service->getBusinessSchedule($startDate, $endDate);

        $events = array_merge($reserve, $schedule);
        $result = ['result_info'=>$resultInfo, 'reserve_info'=>$events];

        return response()->json($result);
    }


    /**
     * 予約登録
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addRserve(Request $request): JsonResponse
    {
        // バリデーション
        $request->validate([
            'start_date' => 'required|integer',
            'end_date' => 'required|integer',
            'user_no' => 'required_without_all:user_name',
            'user_name' => 'required_without_all:user_no',
        ]);

        // 予約情報取得
        $startDate = date('Y-m-d H:i:s', $request->input('start_date') / 1000);
        $endDate = date('Y-m-d H:i:s', $request->input('end_date') / 1000);
        $userNo = $request->input('user_no') ? mb_convert_kana($request->input('user_no'), 'a') : ''; // 全角⇒半角
        $userName = $request->input('user_name');

        $resultInfo = ['code'=>200, 'message'=>''];

        try {
            $memberInfo = [];
            if (!empty($userNo)) {
                // ユーザ情報取得＆チェック
                $memberInfo = $this->service->fetchMember($userNo, $startDate);
            } else {
                // ゲスト予約の場合
                $memberInfo['user_no'] = 'g'; // @@@一旦固定値
                $memberInfo['name'] = $userName;
            }

            // スケジュール取得＆チェック
            $scheduleInfo = $this->service->fetchSchedule($startDate);

            // 予約登録
            $reserveId = $this->service->registRserve($scheduleInfo, $memberInfo, $startDate);
            $memberInfo['reserve_id'] = $reserveId;
            if (!$reserveId) {
                throw new \Exception('予約登録エラー', 500);
            }

        } catch(\Exception $e) {
            Log::error('@@@@@');

            $resultInfo = ['code'=>$e->getCode(), 'message'=>$e->getMessage()];
            Log::error('code:' . $resultInfo['code'] . ' ' . 'message:' . $resultInfo['message']);
        }

        // 0埋め
        $memberInfo['user_no'] = str_pad($memberInfo['user_no'], 3, '0', STR_PAD_LEFT);


        $result = ['result_info'=>$resultInfo, 'user_info'=>$memberInfo];

        return response()->json($result);
    }

    /**
     * 予約キャンセル
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelRserve(Request $request): JsonResponse
    {

        // バリデーション
        $request->validate([
            'user_no' => 'required|string',
            'reserve_id' => 'required|integer',
        ]);

        $resultInfo = ['code'=>200, 'message'=>''];
        // 予約登録
        $isCancel = $this->service->cancelReserve($request->all());
        if (!$isCancel) {
            $resultInfo = ['code'=>500, 'message'=>'予約キャンセルエラー'];
        }
        $result = ['result_info'=>$resultInfo];

        return response()->json($result);
    }

}
