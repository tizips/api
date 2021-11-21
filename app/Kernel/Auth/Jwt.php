<?php

declare(strict_types=1);

namespace App\Kernel\Auth;

use App\Exception\ApiException;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Hyperf\Utils\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Jwt
{
    #[Inject]
    private ContainerInterface $container;

    /**
     * Header 部分参数
     */
    private array $header = [
        'alg' => 'HS512', //生成signature的算法
        'typ' => 'JWT'    //类型
    ];

    /**
     * 「加密令牌」
     */
    private string $key;

    /**
     * Jwt constructor.
     * 从配置文件中读取「加密令牌」，并将其赋值于 $key
     * 如果读取失败，讲抛出异常
     */
    public function __construct()
    {
        $this->key = config('jwt.jwt_secret');
        $this->header['alg'] = config('jwt.algo', 'HS512');

        if (empty($this->key)) {
            ApiException::break('JWT 令牌不存在');
        }
    }

    /**
     * @param string      $iss 令牌发放者
     * @param int         $sub 持有人
     * @param string|null $aud 受众
     * @return string   Jwt 令牌
     */
    public function make(string $iss, int $sub, ?string $aud = null): string
    {
        $timer = time();

        $payload = [
            'iss' => $iss,
            'iat' => $timer,
            'exp' => $timer + config('jwt.jwt_ttl'),
            'sub' => $sub,
            'jti' => md5(Str::random(32) . $timer),
        ];

        if ($aud) $payload['aud'] = $aud;

        return $this->create($payload);
    }

    private function refresh_token($payload): string
    {
        return config('cache.default.prefix') . 'refresh:' . $payload['jti'];
    }

    /**
     * @param $payload
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function refresh($payload): ?string
    {
        $redis = $this->container->get(Redis::class);

        $key = $this->refresh_token($payload);

        $timer = time();

        if (! $redis->exists($key)) {

            $token = $this->make($payload['iss'], $payload['sub'], $payload['aud'] ?? null);

            $ok = $redis->hSetNx($key, 'token', $token);

            if ($ok) {

                $redis->hSet($key, 'time', $timer);
                $redis->expireAt($key, $payload['iat'] + config('jwt.refresh_ttl'));

                return $token;
            }
        }

        if ($redis->exists($key)) {

            $data = $redis->hGetAll($key);

            $time = (int) $data['time'];
            $token = (string) $data['token'];

            if (! $time || ! $token) ApiException::break('令牌信息不存在！');

            if ($timer < $time || $timer - $time > config('jwt.leeway')) {
                ApiException::break('令牌已被刷新！');
            }

            return $token;
        }

        ApiException::break('令牌刷新失败！');

        return null;
    }

    public function refresh_can($payload): bool
    {
        $timer = time();

        return $payload['exp'] <= $timer && $payload['iat'] + config('jwt.refresh_ttl') > $timer;
    }

    /**
     * @param array $payload Jwt 载荷   格式如下「非必须」
     *                       [
     *                       'iss'=>'jwt_admin',  //该JWT的签发者
     *                       'iat'=>time(),  //签发时间
     *                       'exp'=>time()+7200,  //过期时间
     *                       'nbf'=>time()+60,  //该时间之前不接收处理该Token
     *                       'sub'=>'user',  //面向的用户
     *                       'jti'=>md5(randString.time())  //该Token唯一标识
     *                       ]
     * @return string
     */
    private function create(array $payload): string
    {
        if (! is_array($payload)) {
            ApiException::break('加密内容生成失败！');
        }

        $base64header = $this->base64UrlEncode(json_encode($this->header, JSON_UNESCAPED_UNICODE));
        $base64payload = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));

        return $base64header . '.' . $base64payload . '.' . $this->signature(
                $base64header . '.' . $base64payload,
                $this->key,
                $this->header['alg']
            );
    }

    /**
     * @param string $token 签发的授权令牌
     * @return array        授权信息 格式如下
     *                      [
     *                      'iss'=>'jwt_admin',  //该JWT的签发者
     *                      'iat'=>time(),  //签发时间
     *                      'exp'=>time()+7200,  //过期时间
     *                      'nbf'=>time()+60,  //该时间之前不接收处理该Token
     *                      'sub'=>'user',  //面向的用户
     *                      'jti'=>md5(randString.time())  //该Token唯一标识
     *                      ]
     */
    public function parse(string $token): array
    {
        $tokens = explode('.', $token);

        if (count($tokens) != 3) {
            ApiException::break('Unauthorized');
        }

        list($base64header, $base64payload, $sign) = $tokens;

        //获取jwt算法
        $base64DecodeHeader = json_decode($this->base64UrlDecode($base64header), true);
        if (empty($base64DecodeHeader['alg'])) {
            ApiException::break('Unauthorized');
        }

        //签名验证
        if ($this->signature($base64header . '.' . $base64payload, $this->key, $base64DecodeHeader['alg']) !== $sign) {
            ApiException::break('Unauthorized');
        }

        return json_decode($this->base64UrlDecode($base64payload), true);
    }

    public function verify(array $payload)
    {
        $timer = time();

        //签发时间大于当前服务器时间验证失败
        if (isset($payload['iat']) && $payload['iat'] > $timer) {
            ApiException::break('Unauthorized');
        }

        //过期时间小于当前服务器时间验证失败
        if (isset($payload['exp']) && $payload['exp'] < $timer) {
            ApiException::break('Unauthorized');
        }

        //该nbf时间之前不接收处理该Token
//        if (isset($payload['nbf']) && $payload['nbf'] > time()) {
//            ApiException::break('Unauthorized');
//        }
    }

    /**
     * base64UrlEncode https://jwt.io/  中base64UrlEncode解码实现
     * @param string $input 需要转码的字符串
     * @return string
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
     * HMACSHA256 签名   https://jwt.io/  中 HMACSHA256 签名实现
     * @param string $input 为base64UrlEncode(header).".".base64UrlEncode(payload)
     * @param string $key
     * @param string $alg   算法方式
     * @return mixed
     */
    private function signature(string $input, string $key, string $alg = 'HS256'): string
    {
        $alg_config = [
            'HS256' => 'sha256',
            'HS512' => 'sha512',
        ];

        return $this->base64UrlEncode(hash_hmac($alg_config[$alg], $input, $key, true));
    }

}