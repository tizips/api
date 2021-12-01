<?php

declare(strict_types=1);

namespace App\Command\Admin;

use App\Constants\EnableConstants;
use App\Exception\ApiException;
use App\Helper\Casbin;
use App\Model\Admin;
use App\Model\AdminBindRole;
use App\Model\Role;
use Carbon\Carbon;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\DbConnection\Db;
use Psr\Container\ContainerInterface;
use Throwable;

#[Command]
class RootCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('admin:root');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('添加拥有「系统开发人员」角色的管理员');
    }

    public function handle()
    {
        $this->line('请按照提示，输入以下信息!', 'info');

        do {
            $username = $this->ask('请输入登陆用户名');
        } while (! $username);

        do {
            $nickname = $this->ask('请输入昵称');
        } while (! $nickname);

        do {
            $password = $this->ask('请输入登陆密码');
        } while (! $password);

        $admin = Admin::query()->where('username', $username)->first();

        if ($admin) {
            $this->error('该用户名已被使用！');
            return;
        }

        /** @var Role $role */
        $role = Role::query()->where('slug', Casbin::ROOT)->first();

        if (! $role) {
            $this->error('角色信息不存在！');
            return;
        }

        $now = Carbon::now();

        Db::beginTransaction();

        try {
            /** @var Admin $admin */
            $admin = Admin::query()
                ->create([
                    'username' => $username,
                    'nickname' => $nickname,
                    'password' => password_hash($password, PASSWORD_BCRYPT),
                    'is_enable' => EnableConstants::IS_ENABLE_YES,
                    'created_at' => $now->toDateTimeString(),
                    'updated_at' => $now->toDateTimeString(),
                ]);

            if (! $admin) ApiException::break('管理员添加失败！');

            $bind = AdminBindRole::query()
                ->create([
                    'admin_id' => $admin->id,
                    'role_id' => $role->id,
                ]);

            if (! $bind) ApiException::break('绑定信息添加失败！');

            Db::commit();
        } catch (Throwable $exception) {
            Db::rollBack();
            $this->error($exception->getMessage());
        }
    }
}
