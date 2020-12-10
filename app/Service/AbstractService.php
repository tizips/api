<?php

declare(strict_types=1);

namespace App\Service;

use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

abstract class AbstractService
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var CacheInterface
     */
    protected $cache;
}
