<?php

declare(strict_types=1);

namespace App\Exception;


use Hyperf\Server\Exception\ServerException;
use Throwable;

class ApiException extends ServerException
{
    protected mixed $data;

    public function __construct(string $message, int $code = null, Throwable $previous = null)
    {
        parent::__construct($message, $code ?: 600, $previous);
    }

    public static function break(string $message, int $code = null, Throwable $previous = null)
    {
        throw new self($message, $code, $previous);
    }
}