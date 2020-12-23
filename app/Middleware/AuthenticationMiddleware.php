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

class AuthenticationMiddleware implements MiddlewareInterface
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
            ApiException::break(Status::ERR_AUTH);
        }

        Context::set('uid', $payload['sub']);

        return $handler->handle($request);
    }

    private function verifyToken()
    {
        $request = $this->container->get(RequestInterface::class);

        $token = $authorization = '';

        if ($request->hasCookie('Authorization')) {
            $authorization = $request->cookie('Authorization');
            $token = substr($authorization, 7);
        } elseif ($request->hasHeader('Authorization')) {
            $authorization = $request->getHeaderLine('Authorization');
            $token = substr($authorization, 7);
        } elseif ($request->has('Authorization')) {
            $token = $request->input('Authorization');
        }

        return $this->jwt->verifyToken($token);
    }
}