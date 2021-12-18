<?php

declare(strict_types=1);

namespace App\Command\Admin;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;

#[Command]
class KeyCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('admin:key');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('生成 JWT 加密令牌');
    }

    public function handle()
    {
        $this->info('开始更新环境配置文件');

        if (! file_exists($this->file_env())) {
            if (! file_exists($this->file_env_example())) {
                $this->error('环境配置模板文件不存在！');
                return;
            }

            $ok = copy($this->file_env_example(), $this->file_env());

            if (! $ok) {
                $this->error('环境配置文件复制失败！');
                return;
            }
        }

        $contents = file_get_contents($this->file_env());

        $content = explode("\n", $contents);

        foreach ($content as $key => $value) {
            if (str_starts_with($value, $this->key())) {
                $content[$key] = $this->key() . '=' . base64_encode(Str::random(32));
            }
        }

        $data = implode("\n", $content);

        $ok = file_put_contents($this->file_env(), $data);

        if (! $ok) {
            $this->error('保存失败！');
            return;
        }

        $this->info($this->key() . '更新成功');
    }

    private function file_env(): string
    {
        return path_base('/.env');
    }

    private function file_env_example(): string
    {
        return path_base('/.env.example');
    }

    private function key(): string
    {
        return 'JWT_SECRET';
    }
}
