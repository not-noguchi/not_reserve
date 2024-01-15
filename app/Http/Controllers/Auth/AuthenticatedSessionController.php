<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Dao\UserDao;


class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // セッションIDの再発行
        $request->session()->regenerate();

        $user = Auth::user();
        $user->update(['api_token' => Str::uuid()]);
        // ユーザ情報取得
        $tagetDate = Carbon::now();
        $tagetDate = $tagetDate->format('Y-m-d H:i:s');
        $userDao = new UserDao();
        $userInfo = $userDao->fetchUser($user->user_no, $tagetDate);
        // plan_id設定
        $request->session()->put('plan_id', $userInfo->plan_id);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/home');
    }
}
