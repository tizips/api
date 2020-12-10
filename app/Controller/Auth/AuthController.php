<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Common\Api\Response;
use App\Common\Api\Status;
use App\Common\Auth\Jwt;
use App\Controller\AbstractController;
use App\Exception\ApiException;
use App\Request\Auth\JwtTokenRequest;
use App\Service\AdminService;
use Carbon\Carbon;
use Hyperf\Di\Annotation\Inject;

class AuthController extends AbstractController
{
    /**
     * @Inject
     * @var Jwt
     */
    protected $jwt;

    /**
     * @Inject
     * @var Response
     */
    protected $response;

    /**
     * @Inject
     * @var AdminService
     */
    protected $adminService;

    public function doJwtToken(JwtTokenRequest $request)
    {
        $username = (string)$request->input('username');
        $password = (string)$request->input('password');

        $now = Carbon::now();

        $admin = $this->adminService->getAdminByUsername($username, 60);

        if (empty($admin)) {
            throw ApiException::break(Status::ERR_JWT);
        }

        if (!password_verify($password, $admin['password'])) {
            throw ApiException::break(Status::ERR_JWT);
        }

        //  设置 token 有效时间
        $tokenNbf = $now->timestamp;
        $tokenExp = $now->addRealHours(2)->timestamp;

        //  设置缓存时长
        $this->adminService->cacheAdminById($admin['id'], $admin, 7200);

        //  设置 refresh token 有效时间
        $refreshNbf = $now->subHours(1)->timestamp;
        $refreshExp = $now->addRealHours(7)->timestamp;

        //  token / refresh token 生成
        $token = $this->jwt->issueToken('iss', $tokenNbf, $tokenExp, $admin['id']);
        $refresh = $this->jwt->issueToken('ref', $refreshNbf, $refreshExp, $admin['id']);

        $response = [
            'token' => $token,
            'token_expire_time' => $tokenExp,
            'refresh' => $refresh,
            'refresh_expire_time' => $refreshExp,
        ];

        return $this->response->apiSuccess($response);
    }
}
