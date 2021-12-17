<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Constants\EnableConstants;
use App\Controller\AbstractController;
use App\Exception\ApiException;
use App\Kernel\Auth\Jwt;
use App\Model\Admin;
use App\Validator\Admin\Login\doLoginValidator;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class LoginController extends AbstractController
{
    #[Inject]
    private Jwt $Jwt;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function doLogin(): ResponseInterface
    {
        doLoginValidator::make();

        $username = (string) $this->request->input('username');
        $password = (string) $this->request->input('password');

        /** @var Admin $admin */
        $admin = Admin::query()->where('username', $username)->first();

        if (! $admin) ApiException::break('用户名或密码错误');
        if ($admin->is_enable != EnableConstants::IS_ENABLE_YES) ApiException::break('该账号已被禁用！');
        if (! password_verify($password, $admin->password)) ApiException::break('用户名或密码错误');

        $token = $this->Jwt->make(config('app_name'), $admin->id);

        return $this->response->apiSuccess([
            'token' => $token
        ]);
    }
}