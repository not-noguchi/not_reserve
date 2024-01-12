<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckForceLogout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if ($user && $user->is_force_logout) {
            Auth::logout();
            $user->is_force_logout = 0;
            $user->save();
            $request->session()->invalidate(); // セッション削除・再生成（もしかしたら不要かも）
            return redirect(route('login'));
        }

        return $next($request);
    }
}
