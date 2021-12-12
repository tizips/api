<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Exception\ApiException;
use App\Helper\Casbin;
use App\Kernel\Admin\Auth;
use App\Model\Permission;
use App\Model\Role;
use App\Model\RoleBindPermission;
use App\Validator\Admin\Role\CreateValidator;
use App\Validator\Admin\Role\UpdateValidator;
use Donjan\Casbin\Enforcer;
use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\Collection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class RoleController extends AbstractController
{
    /**
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface|Throwable
     */
    public function doCreate(): ResponseInterface
    {
        CreateValidator::make();

        $name = (string) $this->request->input('name');
        $summary = (string) $this->request->input('summary');
        $permission_ids = (array) $this->request->input('permissions');

        $exist = Role::query()->where('name', $name)->exists();
        if ($exist) ApiException::break('已经添加过相同名称的角色！');

        $permissions = Permission::query()
            ->whereNotNull('method')
            ->whereNotNull('path')
            ->where(function (Builder $query) use ($permission_ids) {
                $query->whereIn('id', $permission_ids)
                    ->orWhereIn('parent_i1', $permission_ids)
                    ->orWhereIn('parent_i2', $permission_ids);
            })
            ->get();

        if ($permissions->isEmpty()) ApiException::break('未找到可被添加的权限！');

        Db::beginTransaction();

        try {

            /** @var Role $role */
            $role = Role::query()
                ->create([
                    'name' => $name,
                    'summary' => $summary,
                ]);

            if (! $role) ApiException::break('角色添加失败！');

            $bindings = [];

            foreach ($permissions as $item) {
                /** @var Permission $item */
                $bindings[] = [
                    'role_id' => $role->id,
                    'permission_id' => $item->id,
                ];
            }

            $ok = RoleBindPermission::query()->insert($bindings);
            if (! $ok) ApiException::break('角色添加失败！');

            foreach ($permissions as $item) {
                /** @var Permission $item */
                Enforcer::addPermissionForUser(Casbin::role($role->id), $item->path, $item->method);
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
        UpdateValidator::make();

        $id = (int) $this->request->route('id');
        $name = (string) $this->request->input('name');
        $summary = (string) $this->request->input('summary');
        $permission_ids = (array) $this->request->input('permissions');

        /** @var Role $role */
        $role = Role::query()->with('permissions')->where('id', $id)->first();
        if (! $role) ApiException::break('角色不存在！');

        $exist = Role::query()->where([['name', $name], ['id', '<>', $id]])->exists();
        if ($exist) ApiException::break('已经添加过相同名称的角色！');

        /** @var Permission[]|Collection $permissions */
        $permissions = Permission::query()
            ->whereNotNull('method')
            ->whereNotNull('path')
            ->where(function (Builder $query) use ($permission_ids) {
                $query->whereIn('id', $permission_ids)
                    ->orWhereIn('parent_i1', $permission_ids)
                    ->orWhereIn('parent_i2', $permission_ids);
            })
            ->get();

        if ($permissions->isEmpty()) ApiException::break('未找到可被添加的权限！');

        $permissions_new = $permissions_old = [];

        if ($permissions->isNotEmpty()) {
            foreach ($permissions as $item) {
                /** @var Permission $item */
                $permissions_new[] = $item->method . '|' . $item->path;
            }
        }

        if ($role->permissions->isNotEmpty()) {
            foreach ($role->permissions as $item) {
                $permissions_old[] = $item->method . '|' . $item->path;
            }
        }

        $diff_create = array_diff($permissions_new, $permissions_old);
        $diff_delete = array_diff($permissions_old, $permissions_new);

        $create = $delete = [];

        if ($diff_create) {
            foreach ($diff_create as $val) {
                foreach ($permissions as $item) {
                    $key = $item->method . '|' . $item->path;
                    if ($key == $val) $create[$item->id] = $key;
                }
            }
        }

        if ($diff_delete) {
            foreach ($diff_delete as $val) {
                foreach ($role->permissions as $item) {
                    $key = $item->method . '|' . $item->path;
                    if ($key == $val) $delete[$item->id] = $key;
                }
            }
        }

        Db::beginTransaction();

        try {

            $affected = Role::query()
                ->where('id', $id)
                ->update([
                    'name' => $name,
                    'summary' => $summary,
                ]);

            if ($affected <= 0) ApiException::break('角色修改失败！');

            if ($create) {
                $creates = [];

                foreach ($create as $key => $item) {
                    $creates[] = [
                        'role_id' => $role->id,
                        'permission_id' => $key,
                    ];
                }

                $ok = RoleBindPermission::query()->insert($creates);
                if (! $ok) ApiException::break('角色修改失败！');

                foreach ($create as $item) {
                    $temp = explode('|', $item);
                    if (count($temp) == 2) Enforcer::addPermissionForUser(Casbin::role($role->id), $temp[1], $temp[0]);
                }
            }
            if ($delete) {
                $deletes = [];

                foreach ($delete as $key => $item) {
                    $deletes[] = $key;
                }

                $ok = RoleBindPermission::query()
                    ->where('role_id', $role->id)
                    ->whereIn('permission_id', $deletes)
                    ->delete();
                if (! $ok) ApiException::break('角色修改失败！');

                foreach ($delete as $item) {
                    $temp = explode('|', $item);
                    if (count($temp) == 2) Enforcer::deletePermissionForUser(Casbin::role($role->id), $temp[1], $temp[0]);
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
     */
    public function doDelete(): ResponseInterface
    {
        $id = (int) $this->request->route('id');

        /** @var Role $role */
        $role = Role::query()->where('id', $id)->first();
        if (! $role) ApiException::break('该角色不存在！');

        $affected = Role::query()->where('id', $id)->delete();
        if ($affected <= 0) ApiException::break('角色删除失败！');

        Enforcer::deleteRole(Casbin::role($role->id));

        return $this->response->apiSuccess();
    }

    /**
     * @return ResponseInterface
     */
    public function toPaginate(): ResponseInterface
    {
        $data = [];

        $roles = Role::query()
            ->with('permissions')
            ->whereNull('slug')
            ->paginate();

        if ($roles->isNotEmpty()) {
            foreach ($roles->items() as $item) {
                /** @var Role $item */
                $permission_ids = [];
                foreach ($item->permissions as $value) {
                    $permission_id = [];
                    /** @var Permission $value */
                    if ($value->parent_i1) $permission_id[] = $value->parent_i1;
                    if ($value->parent_i2) $permission_id[] = $value->parent_i2;
                    $permission_id[] = $value->id;
                    $permission_ids[] = $permission_id;
                }
                $data[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'permissions' => $permission_ids,
                    'summary' => $item->summary,
                    'created_at' => $item->created_at,
                ];
            }
        }

        return $this->response->apiPaginate($roles, $data);
    }

    public function toSelf(): ResponseInterface
    {
        $data = [];

        $builder = Role::query();

        if (! Enforcer::hasRoleForUser(Casbin::user(Auth::id()), Casbin::ROOT)) {
            $builder->whereNull('slug');
        }

        /** @var Role[]|Collection $roles */
        $roles = $builder->get();

        if ($roles->isNotEmpty()) {
            foreach ($roles as $item) {
                $data[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                ];
            }
        }

        return $this->response->apiSuccess($data);
    }
}