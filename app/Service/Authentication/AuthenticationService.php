<?php

declare(strict_types=1);

namespace App\Service\Authentication;

use App\Common\Api\Status;
use App\Common\Auth\Jwt;
use App\Exception\ApiException;
use App\Repository\AdminRepository;
use App\Service\AbstractService;
use Carbon\Carbon;
use Hyperf\Di\Annotation\Inject;

class AuthenticationService extends AbstractService
{
    /**
     * @Inject
     * @var Jwt
     */
    protected $jwt;

    /**
     * @Inject
     * @var AdminRepository
     */
    protected $adminRepository;

    public function account(string $username, string $password): array
    {
        $now = Carbon::now();

        $admin = $this->adminRepository->getAdminByUsername($username, 60);

        if (empty($admin) || !password_verify($password, $admin['password'])) {
            ApiException::break(Status::ERR_JWT);
        }

        //  设置 token 有效时间
        $tokenNbf = $now->timestamp;
        $tokenExp = $now->addRealHours(2)->timestamp;

        //  设置缓存时长
        $this->adminRepository->cacheAdminById($admin['id'], $admin, 7200);

        //  设置 refresh token 有效时间
        $refreshNbf = $now->subHours(1)->timestamp;
        $refreshExp = $now->addRealHours(7)->timestamp;

        //  token / refresh token 生成
        $token = $this->jwt->issueToken('iss', $tokenNbf, $tokenExp, $admin['id']);
        $refresh = $this->jwt->issueToken('ref', $refreshNbf, $refreshExp, $admin['id']);

        return [
            'token' => $token,
            'token_expire_time' => $tokenExp,
            'refresh' => $refresh,
            'refresh_expire_time' => $refreshExp,
        ];
    }
}
