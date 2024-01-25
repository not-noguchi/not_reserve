<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Services\User\CalendarService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon;
use Carbon\CarbonImmutable;

/**
 * カレンダーAPI(ユーザー)
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

        $user = Auth::user();
        $reserve = $this->service->fetchReserve($startDate, $endDate, $user->user_no);
        $schedule = $this->service->getBusinessSchedule($startDate, $endDate, $user->user_no);

        $result = ['result_info'=>$resultInfo, 'reserve_info'=>$reserve, 'schedule_info'=>$schedule];

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
            'start_date' => 'required',
        ]);

        $user = Auth::user();

        // 予約情報取得
        $startDate = $request->input('start_date');
        $userNo = $user->user_no;

        $resultInfo = ['code'=>200, 'message'=>''];

        try {
            $userInfo = [];
            if (!empty($userNo)) {
                // ユーザ情報取得＆チェック
                $userInfo = $this->service->fetchMember($userNo, $startDate);
            }

            // 予約チェック
            $reserveCnt = $this->service->fetchReserveCnt($userInfo);
            $mstReserveCnt = config('const.reserve_cnt');
            if ($mstReserveCnt[$userInfo['plan_id']] <= $reserveCnt) {
                // 最大予約数オーバー
                throw new \Exception('予約登録エラー(予約数オーバー)', 500);
            }

            // スケジュール取得＆チェック
            $scheduleInfo = $this->service->fetchSchedule($startDate, $userInfo);

            // 予約登録
            $reserveId = $this->service->registRserve($scheduleInfo, $userInfo, $startDate);
            $userInfo['reserve_id'] = $reserveId;
            if (!$reserveId) {
                throw new \Exception('予約登録エラー', 500);
            }

        } catch(\Exception $e) {
            $resultInfo = ['code'=>$e->getCode(), 'message'=>$e->getMessage()];
            Log::error('code:' . $resultInfo['code'] . ' ' . 'message:' . $resultInfo['message']);
        }

        $result = ['result_info'=>$resultInfo];

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
            'reserve_id' => 'required|integer',
        ]);

        $user = Auth::user();
        $arrRequest = $request->all();
        $arrRequest['user_no'] = $user->user_no;


        $resultInfo = ['code'=>200, 'message'=>''];
        // 予約キャンセル
        $isCancel = $this->service->cancelReserve($arrRequest);
        if (!$isCancel) {
            $resultInfo = ['code'=>500, 'message'=>'予約キャンセルエラー'];
        }
        $result = ['result_info'=>$resultInfo];

        return response()->json($result);
    }

}
