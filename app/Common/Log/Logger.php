<?php

declare(strict_types=1);

namespace App\Common\Log;

use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Psr\Log\LoggerInterface;

/**
 * Class Logger
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
class Logger
{
    const CHANNEL_DEFAULT = 'app';

    /**
     * @param $method
     * @param $arguments
     */
    public static function __callStatic($method, $arguments)
    {
        call_user_func_array(
            [
                ApplicationContext::getContainer()
                    ->get(LoggerFactory::class)
                    ->get(self::CHANNEL_DEFAULT),
                $method
            ],
            $arguments
        );
    }

    /**
     * @param string $name
     * @return LoggerInterface
     */
    public static function channel(string $name = self::CHANNEL_DEFAULT)
    {
        if (empty($name)) {
            $name = self::CHANNEL_DEFAULT;
        }
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($name);
    }
}
