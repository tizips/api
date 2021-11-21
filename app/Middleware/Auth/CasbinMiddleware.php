<?php

declare(strict_types=1);

namespace App\Middleware\Auth;

use App\Helper\Casbin;
use App\Kernel\Auth\Auth;
use Donjan\Casbin\Enforcer;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\HttpServer\Router\Dispatched;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 检测账号权限是否被授权
 */
class CasbinMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected RequestInterface $request;

    protected HttpResponse $response;

    public function __construct(ContainerInterface $container, RequestInterface $request, HttpResponse $response)
    {
        $this->container = $container;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $server = $request->getServerParams();
        $obj = $request->getAttribute(Dispatched::class);
        $route = $obj->handler->route;

        $method = strtoupper($server['request_method']);

        $default = [];

        if (Casbin::DEFAULT) {     //  处理默认放通接口
            foreach (Casbin::DEFAULT as $item) {
                $default[] = sprintf('%s:%s', $item['path'], $item['method']);
            }
            $default = array_unique($default);
        }

        if (Enforcer::hasRoleForUser(Casbin::user(Auth::id()), Casbin::ROOT) || Enforcer::enforce(Casbin::user(Auth::id()), $route, $method) || in_array(sprintf('%s:%s', $route, $method), $default)) {
            return $handler->handle($request);
        }

        return $this->response
            ->withStatus(403)
            ->raw('未授权');
    }
}