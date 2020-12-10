<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Common\Api\Response;
use App\Common\Api\Status;
use Hyperf\HttpMessage\Exception\HttpException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class HttpExceptionHandler
 * @package App\Exception\Handler
 */
class HttpExceptionHandler extends \Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler
{
    /**
     * @inheritDoc
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // 阻止异常冒泡
        $this->stopPropagation();

        /**
         * @var HttpException $throwable
         */
        $code = $throwable->getStatusCode();
        $msg = $throwable->getMessage();
        $apiResponse = (new Response($response->withStatus($code)));

        return $apiResponse
            ->apiError(new Status($code . '00|' . $msg));
    }
}
