<?php

declare(strict_types=1);

namespace App\Service;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Request;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

abstract class AbstractService
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected CacheInterface $cache;

    #[Inject]
    protected Redis $redis;

    #[Inject]
    protected Request $request;
}
