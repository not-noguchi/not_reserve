<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class ReserveController extends Controller
{

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $planId = $request->session()->get('plan_id');
        if ($planId == 0) {
            // プラン未設定
            // セッションが切れるときがあるが予約カレンダーでセッション見ないので何もしない
        }
        //$user = Auth::user();


        return view('reserve', [
//            'user' => Auth::user()
        ]);
    }
}
