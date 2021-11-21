<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Exception\ApiException;
use App\Kernel\Response\Response;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class ApiExceptionHandler
 * @package App\Exception\Handler
 */
class ApiExceptionHandler extends ExceptionHandler
{
    /**
     * @inheritDoc
     */
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        // 阻止异常冒泡
        $this->stopPropagation();

        /**
         * @var ApiException $throwable
         */
        $apiResponse = new Response($response);

        return $apiResponse->withStatus(200)->apiResponse(60000, $throwable->getMessage());
    }

    /**
     * @inheritDoc
     */
    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ApiException;
    }
}
