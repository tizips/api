<?php

declare(strict_types=1);

namespace App\Command\Casbin;

use App\Helper\Casbin;
use App\Helper\Method;
use App\Model\Permission;
use App\Model\Role;
use Carbon\Carbon;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;

#[Command]
class InitCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('casbin:init');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('角色权限初始化');
    }

    public function handle()
    {
        $exist = Role::query()->exists();

        if ($exist) {
            $this->error('角色信息已存在！无法重复生成！');
            return;
        }

        $ok = Role::query()->insert($this->roles());

        if (! $ok) {
            $this->error('角色生成失败！请检查「role」表是否被清空。');
            return;
        }

        $ok = Permission::query()->insert($this->permissions());

        if (! $ok) {
            $this->error('权限生成失败！请检查「permission」表是否被清空。');
            return;
        }

        $this->info('基本角色及权限生成成功！');
    }

    private function roles(): array
    {
        $now = Carbon::now();

        return [
            [
                'id' => 1,
                'slug' => Casbin::ROOT,
                'name' => '开发组',
                'summary' => '系统开发账号所属角色，无需单独权限授权，即可拥有系统所有权限。',
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ]
        ];
    }

    private function permissions(): array
    {
        $now = Carbon::now();

        return [
            [
                'id' => 1,
                'parent_i1' => null,
                'parent_i2' => null,
                'name' => '授权管理',
                'slug' => 'auth',
                'method' => null,
                'path' => null,
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'id' => 2,
                'parent_i1' => 1,
                'parent_i2' => null,
                'name' => '权限管理',
                'slug' => 'auth.permission',
                'method' => null,
                'path' => null,
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'id' => 3,
                'parent_i1' => 1,
                'parent_i2' => 2,
                'name' => '创建',
                'slug' => 'auth.permission.create',
                'method' => Method::POST,
                'path' => '/admin/permission',
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'id' => 4,
                'parent_i1' => 1,
                'parent_i2' => 2,
                'name' => '修改',
                'slug' => 'auth.permission.update',
                'method' => Method::PUT,
                'path' => '/admin/permissions/{id}',
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'id' => 5,
                'parent_i1' => 1,
                'parent_i2' => 2,
                'name' => '删除',
                'slug' => 'auth.permission.delete',
                'method' => Method::DELETE,
                'path' => '/admin/permissions/{id}',
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'id' => 6,
                'parent_i1' => 1,
                'parent_i2' => 2,
                'name' => '列表',
                'slug' => 'auth.permission.tree',
                'method' => Method::GET,
                'path' => '/admin/permissions',
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'id' => 7,
                'parent_i1' => 1,
                'parent_i2' => null,
                'name' => '角色管理',
                'slug' => 'auth.role',
                'method' => null,
                'path' => null,
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'id' => 8,
                'parent_i1' => 1,
                'parent_i2' => 7,
                'name' => '创建',
                'slug' => 'auth.role.create',
                'method' => Method::POST,
                'path' => '/admin/role',
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'id' => 9,
                'parent_i1' => 1,
                'parent_i2' => 7,
                'name' => '修改',
                'slug' => 'auth.role.update',
                'method' => Method::PUT,
                'path' => '/admin/roles/{id}',
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'id' => 10,
                'parent_i1' => 1,
                'parent_i2' => 7,
                'name' => '删除',
                'slug' => 'auth.role.delete',
                'method' => Method::DELETE,
                'path' => '/admin/roles/{id}',
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'id' => 11,
                'parent_i1' => 1,
                'parent_i2' => 7,
                'name' => '列表',
                'slug' => 'auth.role.paginate',
                'method' => Method::GET,
                'path' => '/admin/roles',
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'id' => 12,
                'parent_i1' => 1,
                'parent_i2' => null,
                'name' => '账号管理',
                'slug' => 'auth.admin',
                'method' => null,
                'path' => null,
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'id' => 13,
                'parent_i1' => 1,
                'parent_i2' => 12,
                'name' => '创建',
                'slug' => 'auth.admin.create',
                'method' => Method::POST,
                'path' => '/admin/admin',
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'id' => 14,
                'parent_i1' => 1,
                'parent_i2' => 12,
                'name' => '修改',
                'slug' => 'auth.admin.update',
                'method' => Method::PUT,
                'path' => '/admin/admins/{id}',
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'id' => 15,
                'parent_i1' => 1,
                'parent_i2' => 12,
                'name' => '删除',
                'slug' => 'auth.admin.delete',
                'method' => Method::DELETE,
                'path' => '/admin/admins/{id}',
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'id' => 16,
                'parent_i1' => 1,
                'parent_i2' => 12,
                'name' => '启禁',
                'slug' => 'auth.admin.delete',
                'method' => Method::PUT,
                'path' => '/admin/admin/enable',
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            [
                'id' => 17,
                'parent_i1' => 1,
                'parent_i2' => 12,
                'name' => '列表',
                'slug' => 'auth.admin.paginate',
                'method' => Method::GET,
                'path' => '/admin/admins',
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
        ];
    }
}
