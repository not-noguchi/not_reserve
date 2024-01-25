<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\User\HomeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Dao\UserDao;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{

    /** @var HomeService ホームサービスクラス */
    private $service;

    /**
     * コンストラクタ
     *
     * @param HomeService $service
     */
    public function __construct(HomeService $service)
    {
        $this->service = $service;
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $planId = $request->session()->get('plan_id');
        if ($planId == null) {
            // plan_id設定
            $userDao = new UserDao();
            $tagetDate = Carbon::now()->format('Y-m-d H:i:s');
            $userInfo = $userDao->fetchUser($user->user_no, $tagetDate);
            $request->session()->put('plan_id', $userInfo->plan_id);
            Log::debug('reset user_no:' . $user->user_no);
            Log::debug('reset plan_id:' . $userInfo->plan_id);
        }

        // 予約情報取得
        $reserveInfo = $this->service->fetchReserve($user->user_no);

        return view('home', [
            'reserve_list' => $reserveInfo
        ]);
    }
}
