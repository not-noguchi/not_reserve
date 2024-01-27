<?php

namespace App\Services\Admin;

use App\Dao\ReserveDao;
use App\Dao\ScheduleDao;
use App\Dao\UserDao;
use Illuminate\Support\Facades\Log;
use Carbon\CarbonImmutable;

/**
 * ユーザー管理　（管理）
 */
class UserService
{
    /** @var UserDao ユーザー */
    private $userDao;

    /**
     * コンストラクタ
     *
     * @param ReserveDao $reserveDao
     */
    public function __construct(
        UserDao $userDao
    ) {
        $this->userDao = $userDao;
    }


    /**
     * 会員情報取得
     *
     * @return array
     */
    public function fetchUser(): array
    {
        $result = [];
        $result = $this->userDao->fetchUserListForAdmin();

        if ($result->isEmpty()) {
            // 対象会員Noなし
            throw new \Exception('会員が存在しません。', 422);
        }

        return $result->all();
    }


    /**
     * 会員削除(0件更新の場合、falseを返却)
     *
     * @param string $userId
     * @return bool
     */
    public function deleteUser(string $userId): bool
    {
        // 会員削除
        return $this->userDao->deleteUser($userId);
    }

}
