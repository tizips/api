<?php

declare(strict_types=1);

namespace App\Kernel\Auth;

use App\Model\Admin;
use Hyperf\Utils\Context;

class Auth
{
    /**
     * 获取登录用户 ID
     * @return int
     */
    public static function id(): int
    {
        $payload = self::jwt();
        return $payload ? $payload['sub'] : 0;
    }

    /**
     * 获取登录用户信息
     * @return Admin
     */
    public static function user(): Admin
    {
        return Admin::findFromCache(self::id());
    }

    public static function check(): bool
    {
        return self::id() > 0 && ! empty(self::user());
    }

    /**
     * 获取用户令牌相关信息
     * @return array|null
     */
    public static function jwt(): ?array
    {
        return Context::get('JWT');
    }

    /**
     * 判断用户是否处于开发环境中
     * @return bool
     */
    public static function prod(): bool
    {
        return env('APP_ENV', 'dev') == 'prod';
    }

    public static function blacklist(): string
    {
        return sprintf('jwt:blacklist:%s', Auth::jwt()['jti']);
    }
}