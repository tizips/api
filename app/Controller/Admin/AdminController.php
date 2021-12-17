<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Constants\EnableConstants;
use App\Controller\AbstractController;
use App\Exception\ApiException;
use App\Helper\Casbin;
use App\Kernel\Admin\Auth;
use App\Model\Admin;
use App\Model\AdminBindRole;
use App\Model\Role;
use App\Validator\Admin\Admin\doCreateValidator;
use App\Validator\Admin\Admin\doUpdateValidator;
use App\Validator\Unit\doEnableValidator;
use Donjan\Casbin\Enforcer;
use Exception;
use Hyperf\Database\Query\JoinClause;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\Collection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AdminController extends AbstractController
{
    /**
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface|Throwable
     */
    public function doCreate(): ResponseInterface
    {
        doCreateValidator::make();

        $username = (string) $this->request->input('username');
        $nickname = (string) $this->request->input('nickname');
        $password = (string) $this->request->input('password');
        $signature = (string) $this->request->input('signature');
        $role_ids = (array) $this->request->input('roles');

        $exist = Admin::query()->where('username', $username)->exists();
        if ($exist) ApiException::break('该账号已存在！');

        $builder = Role::query();

        if (! Enforcer::hasRoleForUser(Casbin::user(Auth::id()), Casbin::ROOT)) {
            $builder->whereNull('slug');
        }

        /** @var Role[]|Collection $roles */
        $roles = $builder
            ->whereIn('id', $role_ids)
            ->get();

        if ($roles->isEmpty()) ApiException::break('未找到可被添加的角色信息！');

        Db::beginTransaction();

        try {

            /** @var Admin $admin */
            $admin = Admin::query()
                ->create([
                    'username' => $username,
                    'nickname' => $nickname,
                    'password' => password_hash($password, PASSWORD_BCRYPT),
                    'signature' => $signature,
                ]);

            if (! $admin) ApiException::break('账号添加失败！');

            $bindings = [];

            foreach ($roles as $item) {
                $bindings[] = [
                    'admin_id' => $admin->id,
                    'role_id' => $item->id,
                ];
            }

            $ok = AdminBindRole::query()->insert($bindings);
            if (! $ok) ApiException::break('账号添加失败！');

            foreach ($roles as $item) {
                if ($item->slug == Casbin::ROOT) {
                    Enforcer::addRoleForUser(Casbin::user($admin->id), Casbin::ROOT);
                } else {
                    Enforcer::addRoleForUser(Casbin::user($admin->id), Casbin::role($item->id));
                }
            }

            Db::commit();
        } catch (Throwable $exception) {
            Db::rollBack();
            throw $exception;
        }

        return $this->response->apiSuccess();
    }

    /**
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface|Throwable
     */
    public function doUpdate(): ResponseInterface
    {
        doUpdateValidator::make();

        $id = (int) $this->request->route('id');
        $nickname = (string) $this->request->input('nickname');
        $password = (string) $this->request->input('password');
        $signature = (string) $this->request->input('signature');
        $role_ids = (array) $this->request->input('roles');

        /** @var Admin $admin */
        $admin = Admin::query()->with('roles')->where('id', $id)->first();
        if (! $admin) ApiException::break('账号不存在！');

        $builder = Role::query();

        if (! Enforcer::hasRoleForUser(Casbin::user(Auth::id()), Casbin::ROOT)) {
            $builder->whereNull('slug');
        }

        /** @var Role[]|Collection $roles */
        $roles = $builder
            ->whereIn('id', $role_ids)
            ->get();

        if ($roles->isEmpty()) ApiException::break('未找到可被赋予的角色！');

        $role_root_create = $role_root_delete = null;

        //  检测被添加的角色中，是否包含「系统开发人员」权限
        if (in_array(Casbin::ROOT, array_column($roles->toArray(), 'slug'))) {
            foreach ($roles as $item) {
                if ($item->slug == Casbin::ROOT) {
                    $role_root_create = $item->id;
                    break;
                }
            }
        }

        //  检测被删除的角色中，是否包含「系统开发人员」权限
        if (in_array(Casbin::ROOT, array_column($admin->roles->toArray(), 'slug'))) {
            foreach ($admin->roles as $item) {
                if ($item->slug == Casbin::ROOT) {
                    $role_root_delete = $item->id;
                    break;
                }
            }
        }

        $roles_new = array_column($roles->toArray(), 'id');
        $roles_old = array_column($admin->roles->toArray(), 'id');

        $create = array_diff($roles_new, $roles_old);
        $delete = array_diff($roles_old, $roles_new);

        Db::beginTransaction();

        try {

            $update = [
                'nickname' => $nickname,
                'signature' => $signature,
            ];

            if ($password) $update['password'] = password_hash($password, PASSWORD_BCRYPT);

            $affected = $admin->update($update);
            if ($affected <= 0) ApiException::break('账号修改失败！');

            if ($create) {
                $creates = [];

                foreach ($create as $item) {
                    $creates[] = [
                        'admin_id' => $admin->id,
                        'role_id' => $item,
                    ];
                }

                $ok = AdminBindRole::query()->insert($creates);
                if (! $ok) ApiException::break('账号修改失败！');

                foreach ($create as $item) {
                    if ($role_root_create == $item) {
                        Enforcer::addRoleForUser(Casbin::user($admin->id), Casbin::ROOT);
                    } else {
                        Enforcer::addRoleForUser(Casbin::user($admin->id), Casbin::role($item));
                    }
                }
            }

            if ($delete) {

                $ok = AdminBindRole::query()
                    ->where('admin_id', $admin->id)
                    ->whereIn('role_id', $delete)
                    ->delete();
                if (! $ok) ApiException::break('账号修改失败！');

                foreach ($delete as $item) {
                    if ($role_root_delete == $item) {
                        Enforcer::deleteRoleForUser(Casbin::user($admin->id), Casbin::ROOT);
                    } else {
                        Enforcer::deleteRoleForUser(Casbin::user($admin->id), Casbin::role($item));
                    }
                }
            }

            Db::commit();
        } catch (Throwable $exception) {
            Db::rollBack();
            throw $exception;
        }

        return $this->response->apiSuccess();
    }

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function doDelete(): ResponseInterface
    {
        $id = (int) $this->request->route('id');

        /** @var Admin $admin */
        $admin = Admin::query()->where('id', $id)->first();
        if (! $admin) ApiException::break('账号不存在！');

        $affected = $admin->delete();
        if ($affected <= 0) ApiException::break('账号删除失败！');

        Enforcer::deleteUser(Casbin::user($admin->id));

        return $this->response->apiSuccess();
    }

    /**
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function doEnable(): ResponseInterface
    {
        doEnableValidator::make();

        $id = (int) $this->request->input('id');
        $enable = (int) $this->request->input('enable');

        /** @var Admin $admin */
        $admin = Admin::query()->where('id', $id)->first();
        if (! $admin) ApiException::break('账号不存在！');

        $affected = $admin->update(['is_enable' => $enable]);
        if ($affected <= 0) ApiException::break(sprintf('账号%s失败！', $enable == EnableConstants::IS_ENABLE_YES ? '启用' : '禁用'));

        return $this->response->apiSuccess();
    }

    /**
     * @return ResponseInterface
     */
    public function toPaginate(): ResponseInterface
    {
        $data = [];

        $builder = Admin::query()
            ->select(sprintf('%s.*', Admin::TABLE))
            ->with('roles');

        if (! Enforcer::hasRoleForUser(Casbin::user(Auth::id()), Casbin::ROOT)) {
            $builder
                ->distinct()
                ->leftJoin(AdminBindRole::TABLE, function (JoinClause $query) {
                    $query->on(sprintf('%s.id', Admin::TABLE), '=', sprintf('%s.admin_id', AdminBindRole::TABLE))
                        ->whereNull(sprintf('%s.deleted_at', AdminBindRole::TABLE));
                })
                ->leftJoin(Role::TABLE, function (JoinClause $query) {
                    $query->on(sprintf('%s.role_id', AdminBindRole::TABLE), '=', sprintf('%s.id', Role::TABLE))
                        ->whereNull(sprintf('%s.deleted_at', Role::TABLE));
                })
                ->whereNotNull(sprintf('%s.id', Role::TABLE))
                ->whereNull(sprintf('%s.slug', Role::TABLE));
        }

        $admins = $builder->paginate();

        if ($admins->isNotEmpty()) {
            foreach ($admins->items() as $item) {
                /** @var Admin $item */
                $roles = [];
                foreach ($item->roles as $value) {
                    $roles[] = [
                        'id' => $value->id,
                        'name' => $value->name,
                    ];
                }
                $data[] = [
                    'id' => $item->id,
                    'username' => $item->username,
                    'nickname' => $item->nickname,
                    'signature' => $item->signature,
                    'is_enable' => $item->is_enable,
                    'roles' => $roles,
                    'created_at' => $item->created_at,
                ];
            }
        }

        return $this->response->apiPaginate($admins, $data);
    }
}