<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Common\Api\Response;
use App\Common\Api\Status;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class ValidationExceptionHandler
 * @package App\Exception\Handler
 */
class ValidationExceptionHandler extends \Hyperf\Validation\ValidationExceptionHandler
{
    /**
     * @inheritDoc
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // 阻止异常冒泡
        $this->stopPropagation();

        /** @var ValidationException $throwable */
        $msg = $throwable->validator->errors()->first();
        $apiResponse = new Response();

        return $apiResponse
            ->apiError(new Status(Status::ERR_VALIDATION, $msg));
    }
}
