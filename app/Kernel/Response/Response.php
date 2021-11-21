<?php

declare(strict_types=1);

namespace App\Kernel\Response;

use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\HttpServer\Response as HyperfHttpResponse;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

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
    public function apiSuccess(mixed $data = null): PsrResponseInterface
    {
        return $this->json($this->apiData(20000, 'Success', $data));
    }

    public function apiPaginate(LengthAwarePaginatorInterface $paginate, mixed $data = null): PsrResponseInterface
    {
        return $this->json($this->apiData(20000, 'Success', ['data' => $data, 'size' => $paginate->perPage(), 'page' => $paginate->currentPage(), 'total' => $paginate->total()]));
    }

    public function apiResponse(int $code, string $message, mixed $data = null): PsrResponseInterface
    {
        return $this->json($this->apiData($code, $message, $data));
    }

    /**
     * @param int    $code
     * @param string $message
     * @param null   $data
     * @return array
     */
    public function apiData(int $code, string $message, mixed $data = null): array
    {
        return [
            'code' => $code,
            'message' => $message,
            'data' => $data === null ? (object) [] : $data,
        ];
    }
}
