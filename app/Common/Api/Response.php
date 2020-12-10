<?php

declare(strict_types=1);

namespace App\Common\Api;

use Hyperf\HttpServer\Response as HyperfHttpResponse;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * Class Response
 * @package App\Common\Api
 */
class Response extends HyperfHttpResponse
{
    public function __construct(?PsrResponseInterface $response = null)
    {
        parent::__construct($response);
    }

    /**
     * @param array|Arrayable|Jsonable|null $data
     * @return PsrResponseInterface
     */
    public function apiSuccess($data = null): PsrResponseInterface
    {
        return $this->json($this->apiRes(new Status(Status::SUCCESS), $data));
    }

    /**
     * @param Status $status
     * @param array|Arrayable|Jsonable|null $data
     * @return PsrResponseInterface
     */
    public function apiError(Status $status, $data = null): PsrResponseInterface
    {
        return $this->json($this->apiRes($status, $data));
    }

    /**
     * @param Status $status
     * @param array|Arrayable|Jsonable|null $data
     * @return array
     */
    public function apiRes(Status $status, $data = null)
    {
        if (($data === null) || (is_array($data && empty($data)))) {
            $data = (object)[];
        }

        return [
            'code' => $status->getCode(),
            'message' => $status->getMsg(),
            'data' => $data
        ];
    }
}
