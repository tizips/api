<?php

declare(strict_types=1);

namespace App\Controller;

use App\Kernel\Response\Response;
use Hyperf\Cache\Cache;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected Cache $cache;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected Response $response;
}
