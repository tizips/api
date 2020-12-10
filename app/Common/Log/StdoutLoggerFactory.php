<?php

declare(strict_types=1);

namespace App\Common\Log;

use Psr\Container\ContainerInterface;

/**
 * Class StdoutLoggerFactory
 * @package App\Common\Log
 */
class StdoutLoggerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return Logger::channel('sys');
    }
}
