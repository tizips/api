<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Helper\Casbin;
use App\Helper\Pattern;
use App\Model\Permission;
use App\Service\AbstractService;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Utils\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class HelperService extends AbstractService
{
    /**
     * @param bool $exist
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function toApis(bool $exist = false): array
    {
        $data = [];

        $command = 'describe:routes';

        $params = ["command" => $command];

        // 可以根据自己的需求, 选择使用的 input/output
        $input = new ArrayInput($params);
        $output = new BufferedOutput();

        $application = $this->container->get(ApplicationInterface::class);

        $application->setAutoExit(false);

        // 这种方式: 不会暴露出命令执行中的异常, 不会阻止程序返回
        $exitCode = $application->run($input, $output);

        $apis = [];

        if (! $exitCode) {
            $content = $output->fetch();
            $matches = [];

            preg_match_all(Pattern::ROUTE, $content, $matches);

            if ($matches) {
                $methods = $matches[2];
                $uris = $matches[3];

                foreach ($methods as $key => $value) {
                    $method = explode('|', $value);
                    foreach ($method as $item) {
                        if (! $value) continue;
                        $apis[] = $item . '|' . $uris[$key];
                    }
                }
            }
        }

        $defaults = [];

        if (Casbin::DEFAULT) {
            foreach (Casbin::DEFAULT as $item) {
                $defaults[] = $item['method'] . '|' . $item['path'];
            }
        }

        $diff = array_diff($apis, $defaults);

        if (! $exist) {
            $temp = [];
            $permissions = Permission::query()->whereNotNull('method')->whereNotNull('path')->get();
            if ($permissions->isNotEmpty()) {
                foreach ($permissions as $item) {
                    /** @var Permission $item */
                    $temp[] = $item->method . '|' . $item->path;
                }
            }

            $diff = array_diff($diff, $temp);
        }

        if ($diff) {
            foreach ($diff as $item) {
                $tmp = explode('|', $item);
                $data[] = [
                    'method' => Str::upper($tmp[0]),
                    'path' => $tmp[1],
                ];
            }
        }

        return $data;
    }
}