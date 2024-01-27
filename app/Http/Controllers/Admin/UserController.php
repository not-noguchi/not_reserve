<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    /** @var UserService ユーザー管理サービスクラス */
    private $service;

    /**
     * コンストラクタ
     *
     * @param UserService $service
     */
    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // ユーザ一覧取得
        $userList = $this->service->fetchUser();
        return view('admin_user', [
            'userList' => $userList
        ]);
    }
}
