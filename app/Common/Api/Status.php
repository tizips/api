<?php

declare(strict_types=1);

namespace App\Common\Api;

/**
 * Class Status
 * @package App\Common\Api
 */
class Status
{
    /**
     * API状态：Success
     */
    const SUCCESS = '10000|Success';

    /**
     * API状态：System Error
     * 状态码范围：60000-60999
     * 默认：60000
     */
    const ERR_SYS = '60000|系统错误';
    const ERR_VALIDATION = '60001|参数校验失败';

    const ERR_AUTH = '61000|认证操作失败';
    const ERR_JWT = '60001|用户名或密码错误';
    const ERR_JWT_NOT_EXIST = '61002|JWT 加密令牌不存在';
    const ERR_JWT_PAYLOAD = '61003|JWT 载荷格式错误';
    const ERR_AUTH_SIGN = '60004|签名验证错误';

    /**
     * @var int $code
     */
    private $code;

    /**
     * @var string $msg
     */
    private $msg;

    /**
     * Status constructor.
     * @param string $statusStr
     * @param string $customMsg
     */
    public function __construct(string $statusStr, string $customMsg = '')
    {
        $this->code = $this->parseCode($statusStr);
        $this->msg = $this->parseMsg($statusStr, $customMsg);
    }

    /**
     * 获取状态码
     *
     * @param string $statusStr
     * @return int
     */
    private function parseCode(string $statusStr)
    {
        $code = strstr($statusStr, '|', true);
        if (empty($code) || !is_numeric($code)) {
            // 若状态码不合法，则返回默认状态码
            return (int)strstr(self::ERR_SYS, '|', true);
        }

        return (int)$code;
    }

    /**
     * 获取状态消息
     *
     * @param string $statusStr
     * @param string $msg
     * @return string
     */
    private function parseMsg(string $statusStr, string $msg = '')
    {
        // 若自定义msg不为空，则返回自定义msg
        if (!empty($msg)) {
            return $msg;
        }

        $msg = strstr($statusStr, '|', false);
        if ($msg === false) {
            // 状态格式不规范，则返回默认系统错误消息
            return (string)ltrim(strstr(self::ERR_SYS, '|', false), '|');
        }
        $msg = ltrim($msg, '|');
        if (empty($msg)) {
            // 状态消息为空，则返回默认系统错误消息
            return (string)ltrim(strstr(self::ERR_SYS, '|', false), '|');
        }

        // 返回已定义的的状态消息
        return (string)$msg;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMsg()
    {
        return $this->msg;
    }
}