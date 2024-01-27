<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\CarbonImmutable;

/**
 * ユーザー管理
 */
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
     * ユーザー削除
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        // バリデーション
        $request->validate([
            'user_id' => 'required|integer',
        ]);

        $resultInfo = ['code'=>200, 'message'=>''];
        // ユーザー削除
        $isDelete = $this->service->deleteUser($request->user_id);
        if (!$isDelete) {
            $resultInfo = ['code'=>500, 'message'=>'ユーザー削除エラー'];
        }
        $result = ['result_info'=>$resultInfo];

        return response()->json($result);
    }

}
