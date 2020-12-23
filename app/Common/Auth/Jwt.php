<?php

declare(strict_types=1);

namespace App\Common\Auth;

use App\Common\Api\Status;
use App\Exception\ApiException;
use Carbon\Carbon;
use Hyperf\Utils\Str;

class Jwt
{
    /**
     * Header 部分参数
     */
    const HEADER = [
        'alg' => 'HS256', //生成signature的算法
        'typ' => 'JWT'    //类型
    ];

    /**
     * 「加密令牌」
     */
    private $key;

    /**
     * Jwt constructor.
     * 从配置文件中读取「加密令牌」，并将其赋值于 $key
     * 如果读取失败，讲抛出异常
     */
    public function __construct()
    {
        $this->key = config('jwt_token');

        if (empty($this->key)) {
            ApiException::break(Status::ERR_JWT_NOT_EXIST);
        }
    }

    /**
     * @param string $iss 令牌发放者
     * @param int $nbf 在此之前不处理
     * @param int $exp 过期时间
     * @param string $sub 持有人
     * @return string   Jwt 令牌
     */
    public function issueToken(string $iss, int $nbf, int $exp, $sub): string
    {
        $now = Carbon::now();

        $payload = [
            'iss' => $iss,
            'iat' => $now->timestamp,
            'nbf' => $nbf,
            'exp' => $exp,
            'sub' => $sub,
            'jti' => md5(Str::random(32) . $now->timestamp),
        ];

        return $this->getToken($payload);
    }

    /**
     * @param array $payload Jwt 载荷   格式如下「非必须」
     * [
     *  'iss'=>'jwt_admin',  //该JWT的签发者
     *  'iat'=>time(),  //签发时间
     *  'exp'=>time()+7200,  //过期时间
     *  'nbf'=>time()+60,  //该时间之前不接收处理该Token
     *  'sub'=>'user',  //面向的用户
     *  'jti'=>md5(randString.time())  //该Token唯一标识
     * ]
     * @return string
     */
    private function getToken(array $payload): string
    {
        if (!is_array($payload)) {
            ApiException::break(Status::ERR_JWT_PAYLOAD);
        }

        $base64header = $this->base64UrlEncode(json_encode(self::HEADER, JSON_UNESCAPED_UNICODE));
        $base64payload = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));
        return $base64header . '.' . $base64payload . '.' . $this->signature(
                $base64header . '.' . $base64payload,
                $this->key,
                self::HEADER['alg']
            );
    }

    /**
     * @param string $token 签发的授权令牌
     * @return array        授权信息 格式如下
     * [
     *  'iss'=>'jwt_admin',  //该JWT的签发者
     *  'iat'=>time(),  //签发时间
     *  'exp'=>time()+7200,  //过期时间
     *  'nbf'=>time()+60,  //该时间之前不接收处理该Token
     *  'sub'=>'user',  //面向的用户
     *  'jti'=>md5(randString.time())  //该Token唯一标识
     * ]
     */
    public function verifyToken(string $token): array
    {
        $tokens = explode('.', $token);
        if (count($tokens) != 3) {
            ApiException::break(Status::ERR_AUTH);
        };

        list($base64header, $base64payload, $sign) = $tokens;

        //获取jwt算法
        $base64DecodeHeader = json_decode($this->base64UrlDecode($base64header), true);
        if (empty($base64DecodeHeader['alg'])) {
            ApiException::break(Status::ERR_AUTH);
        };

        //签名验证
        if ($this->signature($base64header . '.' . $base64payload, $this->key, $base64DecodeHeader['alg']) !== $sign) {
            ApiException::break(Status::ERR_AUTH);
        };

        $payload = json_decode($this->base64UrlDecode($base64payload), true);

        //签发时间大于当前服务器时间验证失败
        if (isset($payload['iat']) && $payload['iat'] > time()) {
            ApiException::break(Status::ERR_AUTH);
        };

        //过期时间小宇当前服务器时间验证失败
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            ApiException::break(Status::ERR_AUTH);
        };

        //该nbf时间之前不接收处理该Token
        if (isset($payload['nbf']) && $payload['nbf'] > time()) {
            ApiException::break(Status::ERR_AUTH);
        };

        return $payload;
    }

    /**
     * base64UrlEncode https://jwt.io/  中base64UrlEncode解码实现
     * @param string $input 需要转码的字符串
     * @return mixed|string
     */
    private function base64UrlEncode(string $input): string
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * base64UrlEncode  https://jwt.io/  中base64UrlEncode解码实现
     * @param string $input 需要解码的字符串
     * @return string
     */
    private function base64UrlDecode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $addlen = 4 - $remainder;
            $input .= str_repeat('=', $addlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * HMACSHA256签名   https://jwt.io/  中HMACSHA256签名实现
     * @param string $input 为base64UrlEncode(header).".".base64UrlEncode(payload)
     * @param string $key
     * @param string $alg 算法方式
     * @return mixed
     */
    private function signature(string $input, string $key, string $alg = 'HS256'): string
    {
        $alg_config = [
            'HS256' => 'sha256'
        ];

        return $this->base64UrlEncode(hash_hmac($alg_config[$alg], $input, $key, true));
    }

}