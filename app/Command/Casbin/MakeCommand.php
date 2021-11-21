<?php

declare(strict_types=1);

namespace App\Command\Casbin;

use App\Helper\Casbin;
use App\Model\Admin;
use App\Model\Role;
use Donjan\Casbin\Enforcer;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Database\Model\Collection;
use Psr\Container\ContainerInterface;

#[Command]
class MakeCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('casbin:make');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('生成 Casbin 缓存数据');
    }

    public function handle()
    {
        $this->info('开始生成 Casbin 缓存...');

        $this->doSynCasbinByRoleWithPermission();

        $this->doSynCasbinByAdminWithRole();

        $this->info('Casbin 缓存数据生成成功！');
    }

    private function doSynCasbinByRoleWithPermission()
    {
        $this->line('开始检测角色权限信息...');

        /** @var Role[]|Collection $roles */
        $roles = Role::query()->with('permissions')->get();

        if ($roles->isNotEmpty()) {
            $this->line('即将生成角色权限缓存');
            $count = 0;
            foreach ($roles as $item) {
                $count += $item->permissions->count();
            }

            $bar = $this->output->createProgressBar($count);

            $bar->start();

            foreach ($roles as $item) {
                foreach ($item->permissions as $value) {
                    Enforcer::addPermissionForUser(Casbin::role($item->id), $value->path, $value->method);
                    $bar->advance();
                }
            }

            $bar->finish();
        }

        $this->line('');
        $this->line('角色权限信息已缓存完成！');
    }

    private function doSynCasbinByAdminWithRole()
    {
        $this->line('开始生成用户角色缓存...');

        /** @var Admin[]|Collection $admins */
        $admins = Admin::query()->with('roles')->get();

        if ($admins->isNotEmpty()) {
            $this->line('即将生成用户角色缓存...');

            $count = 0;
            foreach ($admins as $item) {
                $count += $item->roles->count();
            }

            $bar = $this->output->createProgressBar($count);

            $bar->start();

            foreach ($admins as $item) {
                foreach ($item->roles as $value) {
                    $key = $value->slug === Casbin::ROOT ? Casbin::ROOT : Casbin::role($value->id);
                    Enforcer::addRoleForUser(Casbin::user($item->id), $key);
                    $bar->advance();
                }
            }

            $bar->finish();
        }

        $this->line('');
        $this->line('用户角色信息已缓存完成！');
    }
}
