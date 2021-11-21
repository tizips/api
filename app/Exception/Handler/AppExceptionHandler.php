<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Kernel\Auth\Auth;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Logger\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    protected StdoutLoggerInterface $logger;

    protected LoggerInterface $log;

    public function __construct(StdoutLoggerInterface $logger, LoggerFactory $loggerFactory)
    {
        $this->logger = $logger;
        $this->log = $loggerFactory->get('exception');
    }

    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        if (Auth::prod()) { //  生产环境，报错信息纪录到文件
            $this->log->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
            $this->log->error($throwable->getTraceAsString());
        } else {    //  开发环境，报错信息直接输出到控制台
            $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
            $this->logger->error($throwable->getTraceAsString());
        }

        return $response
            ->withHeader('Server', 'Uper')
            ->withStatus(500)
            ->withBody(new SwooleStream('Internal Server Error.'));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
