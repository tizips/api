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
use App\Service\Admin\HelperService;
use App\Service\Admin\PermissionService;
use App\Validator\Admin\Permission\doCreateValidator;
use App\Validator\Admin\Permission\doUpdateValidator;
use Donjan\Casbin\Enforcer;
use Hyperf\Database\Query\JoinClause;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class PermissionController extends AbstractController
{
    #[Inject]
    private HelperService $HelperService;

    #[Inject]
    private PermissionService $PermissionService;

    /**
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function doCreate(): ResponseInterface
    {
        doCreateValidator::make();

        $parent_id = (int) $this->request->input('parent');
        $name = (string) $this->request->input('name');
        $slug = (string) $this->request->input('slug');
        $method = (string) $this->request->input('method');
        $path = (string) $this->request->input('path');

        if ($method && $path) {
            $apis = $this->HelperService->toApis();

            if ($apis) {
                foreach ($apis as $key => $item) {
                    $apis[$key] = $item['method'] . '|' . $item['path'];
                }

                if (! in_array($method . '|' . $path, $apis)) ApiException::break('该接口不存在！');
            }
        }

        $parent_i1 = $parent_i2 = null;

        if ($parent_id > 0) {
            /** @var Permission $parent */
            $parent = Permission::query()->where('id', $parent_id)->first();

            if ($parent->parent_i2) ApiException::break('该父级已是最低等级，无法继续添加！');

            if ($parent->parent_i1) {
                $parent_i1 = $parent->parent_i1;
                $parent_i2 = $parent->id;

                if (! $method || ! $path) ApiException::break('接口不能为空！');
            } else {
                $parent_i1 = $parent->id;
            }
        }

        $creates = [
            'name' => $name,
            'slug' => $slug,
        ];

        if ($parent_i1) $creates['parent_i1'] = $parent_i1;
        if ($parent_i2) $creates['parent_i2'] = $parent_i2;
        if ($method) $creates['method'] = $method;
        if ($path) $creates['path'] = $path;

        $permission = Permission::query()->create($creates);

        if (! $permission) ApiException::break('权限添加失败！');

        return $this->response->apiSuccess();
    }

    /**
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function doUpdate(): ResponseInterface
    {
        doUpdateValidator::make();

        $id = (int) $this->request->route('id');
        $parent_id = (int) $this->request->input('parent');
        $name = (string) $this->request->input('name');
        $slug = (string) $this->request->input('slug');
        $method = (string) $this->request->input('method');
        $path = (string) $this->request->input('path');

        /** @var Permission $permission */
        $permission = Permission::query()->where('id', $id)->first();
        if (! $permission) ApiException::break('该权限不存在！');

        if ($method && $path) {
            $apis = $this->HelperService->toApis();

            if ($apis) {
                foreach ($apis as $key => $item) {
                    $apis[$key] = $item['method'] . '|' . $item['path'];
                }

                if (! in_array($method . '|' . $path, $apis)) ApiException::break('该接口不存在！');
            }
        }

        $parent_i1 = $parent_i2 = null;

        if ($parent_id > 0) {
            /** @var Permission $parent */
            $parent = Permission::query()->where('id', $parent_id)->first();

            if ($parent->parent_i2) ApiException::break('该父级已是最低等级，无法继续添加！');

            if ($parent->parent_i1) {
                $parent_i1 = $parent->parent_i1;
                $parent_i2 = $parent->id;

                if (! $method || ! $path) ApiException::break('接口不能为空！');
            } else {
                $parent_i1 = $parent->id;
            }
        }

        $updates = [
            'name' => $name,
            'slug' => $slug,
        ];

        if ($parent_i1) $updates['parent_i1'] = $parent_i1;
        if ($parent_i2) $updates['parent_i2'] = $parent_i2;
        if ($method) $updates['method'] = $method;
        if ($path) $updates['path'] = $path;

        $affected = Permission::query()->where('id', $id)->update($updates);

        if ($affected <= 0) ApiException::break('权限修改失败！');

        if ($method != $permission->method || $path != $permission->path) {

            if ($permission->method && $permission->path) {
                //  删除 Casbin 相关权限
                Enforcer::deletePermission($permission->path, $permission->method);
            }

            //  为包含该权限的角色重新生成新的 Casbin 权限
            $roles = Role::query()
                ->distinct()
                ->select(sprintf('%s.*', Role::TABLE))
                ->leftJoin(RoleBindPermission::TABLE, function (JoinClause $query) {
                    $query->on(sprintf('%s.id', Role::TABLE), '=', sprintf('%s.role_id', RoleBindPermission::TABLE))
                        ->whereNull(sprintf('%s.deleted_at', RoleBindPermission::TABLE));
                })
                ->whereNotNull(sprintf('%s.id', RoleBindPermission::TABLE))
                ->where(sprintf('%s.permission_id', RoleBindPermission::TABLE), $id)
                ->get();

            if ($roles->isNotEmpty()) {
                foreach ($roles as $item) {
                    /** @var Role $item */
                    Enforcer::addPermissionForUser(Casbin::role($item->id), $path, $method);
                }
            }
        }

        return $this->response->apiSuccess();
    }

    /**
     * @return ResponseInterface
     */
    public function doDelete(): ResponseInterface
    {
        $id = (int) $this->request->route('id');

        /** @var Permission $permission */
        $permission = Permission::query()->where('id', $id)->first();
        if (! $permission) ApiException::break('该权限不存在！');

        $affected = Permission::query()
            ->where('id', $id)
            ->delete();

        if ($affected <= 0) ApiException::break('权限删除失败！');

        if ($permission->method && $permission->path) Enforcer::deletePermission($permission->path, $permission->method);

        return $this->response->apiSuccess();
    }

    /**
     * @return ResponseInterface
     */
    public function toTree(): ResponseInterface
    {
        $data = $this->PermissionService->toTree();

        return $this->response->apiSuccess($data);
    }

    /**
     * @return ResponseInterface
     */
    public function toParents(): ResponseInterface
    {
        $data = $this->PermissionService->toTree(parent: true);

        return $this->response->apiSuccess($data);
    }

    public function toSelf(): ResponseInterface
    {
        $permissions = $this->PermissionService->toTree(simple: true);

        if (Enforcer::hasRoleForUser(Casbin::user(Auth::id()), Casbin::ROOT)) {
            //  系统开发人员，返回所有权限
            $data = $permissions;
        } else {
            //  获取用户基本权限
            $permissions = $this->PermissionService->toSelf(Auth::id());

            //  获取基本权限的一二级权限 ID
            $parent_i1s = array_unique(array_column($permissions->toArray(), 'parent_i1'));
            $parent_i2s = array_unique(array_column($permissions->toArray(), 'parent_i2'));

            //  获取基本权限的一二级权限
            $permissions_i1 = Permission::query()->whereIn('id', $parent_i1s)->get();
            $permissions_i2 = Permission::query()->whereIn('id', $parent_i2s)->get();

            $permissions = $permissions->merge($permissions_i1)->merge($permissions_i2);

            $data = $this->PermissionService->toHandler($permissions);
        }

        return $this->response->apiSuccess($data);
    }
}