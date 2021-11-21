<?php

declare(strict_types=1);

namespace App\Command\Casbin;

use App\Helper\Casbin;
use App\Model\Role;
use Donjan\Casbin\Enforcer;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Utils\Collection;
use Psr\Container\ContainerInterface;

#[Command]
class ClearCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('casbin:clear');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('清除 Casbin 缓存数据');
    }

    public function handle()
    {
        $this->info('开始执行清除操作...');
        $this->doClear();
    }

    private function doClear()
    {
        $this->info('开始检测角色数据...');

        /** @var Collection|Role[] $roles */
        $roles = Role::query()->get();

        $admins = $roles_casbin = [];

        if ($roles->isNotEmpty()) {
            foreach ($roles as $item) {
                $roles_casbin[] = $item->slug != Casbin::ROOT ? Casbin::role($item->id) : Casbin::ROOT;
            }
        }

        $roles_casbin = array_merge($roles_casbin, Enforcer::getAllRoles());
        $roles_casbin = array_unique($roles_casbin);

        $this->info('角色数据检测完成！');

        if ($roles_casbin) {
            $this->info('正在检测已缓存的用户数据...');
            foreach ($roles_casbin as $item) {
                $admins = array_merge($admins, Enforcer::getUsersForRole($item));
            }
            $admins = array_unique($admins);
            $this->info('检测完成已缓存的用户数据！');
        }


        if ($admins) {  //  删除已生成的用户角色
            $this->info('开始删除已缓存的用户角色映射信息...');
            foreach ($admins as $item) {
                Enforcer::deleteRolesForUser($item);
            }
            $this->info('已删除缓存的用户角色映射信息！');
        }

        if ($roles_casbin) {   //  删除已生成的角色
            $this->info('开始删除已缓存的角色信息...');
            foreach ($roles_casbin as $item) {
                Enforcer::deleteRole($item);
            }
            $this->info('已删除缓存的角色信息！');
        }

        $this->info('Casbin 缓存数据清除成功！');
    }
}
