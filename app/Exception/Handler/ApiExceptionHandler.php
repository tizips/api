<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Common\Api\Response;
use App\Common\Log\StdoutLogger;
use App\Exception\ApiException;
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
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // 阻止异常冒泡
        $this->stopPropagation();

        StdoutLogger::error(
            sprintf(
                "%s(%s): %s\n%s",
                $throwable->getFile(),
                $throwable->getLine(),
                $throwable->getMessage(),
                $throwable->getTraceAsString()
            )
        );

        /**
         * @var ApiException $throwable
         */
        $apiResponse = new Response($response);

        return $apiResponse->apiError($throwable->status, $throwable->getData());
    }

    /**
     * @inheritDoc
     */
    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ApiException;
    }
}
