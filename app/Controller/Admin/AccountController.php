<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Exception\ApiException;
use App\Helper\Casbin;
use App\Kernel\Auth\Auth;
use App\Model\Admin;
use App\Model\Permission;
use App\Service\Admin\PermissionService;
use Donjan\Casbin\Enforcer;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\InvalidArgumentException;

class AccountController extends AbstractController
{
    #[Inject]
    private PermissionService $PermissionService;

    public function toAccount(): ResponseInterface
    {
        $data = [
            'username' => Auth::user()->username,
            'nickname' => Auth::user()->nickname,
            'avatar' => Auth::user()->avatar,
            'email' => Auth::user()->email,
            'signature' => Auth::user()->signature,
            'mobile' => Auth::user()->mobile,
        ];

        return $this->response->apiSuccess($data);
    }

    public function doUpdate(): ResponseInterface
    {
        $avatar = $this->request->input('avatar');
        $nickname = $this->request->input('nickname');
        $mobile = $this->request->input('mobile');
        $email = $this->request->input('email');
        $password = $this->request->input('password');
        $signature = $this->request->input('signature');

        $update = [
            'avatar' => $avatar,
            'nickname' => $nickname,
            'mobile' => $mobile,
            'email' => $email,
            'signature' => $signature,
        ];

        if ($password) $update['password'] = password_hash($password, PASSWORD_BCRYPT);

        $affected = Auth::user()->update($update);
        if ($affected <= 0) ApiException::break('信息修改失败！');

        return $this->response->apiSuccess();
    }

    /**
     * @return ResponseInterface
     * @throws InvalidArgumentException
     */
    public function doLogout(): ResponseInterface
    {
        $this->cache->set(Auth::blacklist(), Auth::jwt(), Auth::jwt()['exp'] - time());

        return $this->response->apiSuccess();
    }

    public function toPermission(): ResponseInterface
    {
        $data = [];

        if (Enforcer::hasRoleForUser(Casbin::user(Auth::id()), Casbin::ROOT)) {
            $permissions = Permission::query()->whereNotNull('method')->whereNotNull('path')->get();

        } else {
            $permissions = $this->PermissionService->toSelf(Auth::id());
        }

        if ($permissions->isNotEmpty()) {
            $data = array_column($permissions->toArray(), 'slug');
        }

        return $this->response->apiSuccess($data);
    }
}