<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Common\Api\Status;
use App\Common\Auth\Jwt;
use App\Exception\ApiException;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthApiMiddleware implements MiddlewareInterface
{
    /**
     * @Inject
     * @var Jwt
     */
    protected $jwt;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $payload = $this->verifyToken();

        if ($payload['iss'] !== 'iss') {
            throw ApiException::break(Status::ERR_AUTH);
        }

        Context::set('uid', $payload['sub']);

        return $handler->handle($request);
    }

    private function verifyToken()
    {
        $request = $this->container->get(RequestInterface::class);

        $token = $authorization = '';

        if ($request->cookie('Authorization')) {
            $authorization = $request->cookie('Authorization');
            $token = substr($authorization, 7);
        } elseif ($request->getHeader('Authorization')) {
            $authorization = $request->header('Authorization');
            $token = substr($authorization, 7);
        } elseif ($request->input('Authorization')) {
            $token = $request->input('Authorization');
        }

        return $this->jwt->verifyToken($token);
    }
}