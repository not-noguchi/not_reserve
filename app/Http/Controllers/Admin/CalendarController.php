<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\CalendarService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{

    /** @var CalendarService カレンダーサービスクラス */
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
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {

        return view('admin_calendar', [
//            'masterSchedule' => $masterSchedule
        ]);
    }
}
