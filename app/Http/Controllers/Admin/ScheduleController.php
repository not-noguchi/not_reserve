<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

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
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // マスタスケジュール取得
        $masterSchedule = $this->service->fetchMasterSchedule();
        return view('admin_schedule', [
            'masterSchedule' => $masterSchedule
        ]);
    }
}
