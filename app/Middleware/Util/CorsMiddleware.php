<?php

declare(strict_types=1);

namespace App\Middleware\Util;

use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (config('cors')) {   //  根据配置文件，判断是否开启跨域

            $response = Context::get(ResponseInterface::class);
            $response = $response->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Credentials', 'true')
                ->withHeader('Access-Control-Allow-Headers', 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization');

            Context::set(ResponseInterface::class, $response);

            if ($request->getMethod() == 'OPTIONS') {
                return $response;
            }
        }

        return $handler->handle($request);
    }
}
