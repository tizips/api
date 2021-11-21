<?php

declare(strict_types=1);

namespace App\Middleware\Auth;

use App\Constants\EnableConstants;
use App\Exception\ApiException;
use App\Kernel\Auth\Auth;
use App\Kernel\Auth\Jwt;
use App\Model\Admin;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

/**
 * 检测用户信息是否真实有效
 */
class AuthMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected Jwt $Jwt;

    protected ContainerInterface $container;

    protected RequestInterface $request;

    protected HttpResponse $response;

    public function __construct(ContainerInterface $container, RequestInterface $request, HttpResponse $response)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws InvalidArgumentException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            //  检测是否登陆
            if (! Auth::check()) ApiException::break('Unauthorized');

            //  验证令牌
            $this->Jwt->verify(Auth::jwt());

            $cache = $this->container->get(CacheInterface::class);
            if ($cache->has(Auth::blacklist())) ApiException::break('Unauthorized');

            /** @var Admin $admin */
            $admin = Admin::findFromCache(Auth::id());

            //  检测账号是否存在或被拉黑
            if (! $admin || $admin->is_enable != EnableConstants::IS_ENABLE_YES) ApiException::break('Unauthorized');

        } catch (Throwable $exception) {
            return $this->response
                ->withStatus(401)
                ->raw($exception->getMessage());
        }

        return $handler->handle($request);
    }
}