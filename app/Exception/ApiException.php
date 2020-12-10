<?php

declare(strict_types=1);

namespace App\Exception;

use App\Common\Api\Status;
use Hyperf\Server\Exception\ServerException;
use Throwable;

/**
 * Class ApiException
 * @package App\Exception
 */
class ApiException extends ServerException
{
    /**
     * @var Status $status
     */
    public $status;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * ApiException constructor.
     * @param Status $status
     * @param mixed|null $data
     * @param Throwable|null $previous
     */
    public function __construct(Status $status, $data = null, Throwable $previous = null)
    {
        $this->status = $status;
        $this->data = $data;
        parent::__construct($this->status->getMsg(), $this->status->getCode(), $previous);
    }


    /**
     * Author: Song
     * Date: 2020/9/22
     * @return mixed|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 终止业务逻辑，并抛出一个异常
     * 后续全局异常处理(App\Exception\Handler\ApiExceptionHandler)将会处理这个异常，
     * 并以统一的格式返回响应，例：
     * ```json
     * {
     *     "code": 50000,
     *     "msg": "Internal Server Error",
     *     "data": {}
     * }
     * ```
     *
     * @param string $statusStr
     * @param string $customMsg
     * @param mixed|null $data
     * @param Throwable|null $previous
     * @throws ApiException
     */
    public static function break(string $statusStr, string $customMsg = '', $data = null, Throwable $previous = null)
    {
        throw new self(new Status($statusStr, $customMsg), $data, $previous);
    }

}
