<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\CarbonImmutable;

/**
 * スケジュール設定API(管理)
 */
class ScheduleController extends Controller
{
    /** @var ScheduleService スケジュール設定サービスクラス */
    private $service;

    /**
     * コンストラクタ
     *
     * @param ScheduleService $service
     */
    public function __construct(ScheduleService $service)
    {
        $this->service = $service;
    }

    /**
     * スケジュール情報取得
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
     * スケジュール登録
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse
    {
        // バリデーション
        $request->validate([
            'm_schedule_id' => 'required|integer',
            'use_date' => 'required|integer',
        ]);
        $resultInfo = ['code'=>200, 'message'=>''];

        try {
            $scheduleInfo = [];

            // スケジュール登録
            $scheduleId = $this->service->registSchedule($request->all());
            $scheduleInfo['schedule_id'] = $scheduleId;
            if (!$scheduleId) {
                throw new \Exception('スケジュール登録エラー', 500);
            }

        } catch(\Exception $e) {
            $resultInfo = ['code'=>$e->getCode(), 'message'=>$e->getMessage()];
            Log::error('code:' . $resultInfo['code'] . ' ' . 'message:' . $resultInfo['message']);
        }

        $result = ['result_info'=>$resultInfo, 'schedule_info'=>$scheduleInfo];

        return response()->json($result);
    }

    /**
     * スケジュール削除
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        // バリデーション
        $request->validate([
            'schedule_id' => 'required|integer',
        ]);

        $resultInfo = ['code'=>200, 'message'=>''];
        // スケジュール削除
        $isDelete = $this->service->deleteSchedule($request->all());
        if (!$isDelete) {
            $resultInfo = ['code'=>500, 'message'=>'スケジュール削除エラー'];
        }
        $result = ['result_info'=>$resultInfo];

        return response()->json($result);
    }

}
