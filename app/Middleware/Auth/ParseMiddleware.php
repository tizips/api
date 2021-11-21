<?php

declare(strict_types=1);

namespace App\Middleware\Auth;

use App\Exception\ApiException;
use App\Kernel\Auth\Jwt;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\Utils\Context;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 解析用户令牌信息
 */
class ParseMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected RequestInterface $request;

    protected HttpResponse $response;

    #[Inject]
    protected Jwt $Jwt;

    public function __construct(ContainerInterface $container, RequestInterface $request, HttpResponse $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {

            $payload = $this->doParseToken();

            if ($payload) {

                if ($this->Jwt->refresh_can($payload)) {

                    $token = $this->Jwt->refresh($payload);
                    $payload = $this->Jwt->parse($token);

                    $response = $this->response->withHeader('Authorization', $token);

                    Context::set(ResponseInterface::class, $response);
                }

                Context::set('JWT', $payload);
            }
        } catch (ApiException $exception) {
            return $this->response
                ->withStatus(401)
                ->raw($exception->getMessage());
        }

        return $handler->handle($request);
    }

    /**
     * 解析验证 Jwt 令牌
     * @return array
     */
    private function doParseToken(): array
    {
        $token = null;

        if ($this->request->hasHeader('Authorization')) {
            $token = $this->request->getHeaderLine('Authorization');
        } else if ($this->request->hasCookie('Authorization')) {
            $token = $this->request->cookie('Authorization');
        } elseif ($this->request->has('Authorization')) {
            $token = $this->request->input('Authorization');
        }

        return $token ? $this->Jwt->parse($token) : [];
    }
}