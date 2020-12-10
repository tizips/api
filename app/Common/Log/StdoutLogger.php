<?php

declare(strict_types=1);

namespace App\Common\Log;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\ApplicationContext;

/**
 * Class StdoutLogger
 * @package App\Common\Log
 *
 * @method static void emergency(string $message, array $context = [])
 * @method static void alert(string $message, array $context = [])
 * @method static void critical(string $message, array $context = [])
 * @method static void error(string $message, array $context = [])
 * @method static void warning(string $message, array $context = [])
 * @method static void notice(string $message, array $context = [])
 * @method static void info(string $message, array $context = [])
 * @method static void debug(string $message, array $context = [])
 * @method static void log($level, string $message, array $context = [])
 */
class StdoutLogger
{
    /**
     * @param $method
     * @param $arguments
     */
    public static function __callStatic($method, $arguments)
    {
        call_user_func_array(
            [
                ApplicationContext::getContainer()
                    ->get(StdoutLoggerInterface::class),
                $method
            ],
            $arguments
        );
    }
}
