<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Model\AdminBindRole;
use App\Model\Permission;
use App\Model\Role;
use App\Model\RoleBindPermission;
use App\Service\AbstractService;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Query\JoinClause;

class PermissionService extends AbstractService
{
    public function toTree(bool $parent = false, bool $simple = false): array
    {
        $builder = Permission::query();

        if ($parent) {
            $builder
                ->whereNull('parent_i2')
                ->whereNull('method')
                ->whereNull('path');
        }

        $permissions = $builder->get();

        return $this->toHandler($permissions, $parent, $simple);
    }

    public function toHandler(Collection|array $permissions, bool $parent = false, bool $simple = false): array
    {
        $data = [];

        if ($permissions->isNotEmpty()) {
            $children_1 = $children_2 = [];

            foreach ($permissions as $item) {
                /** @var Permission $item */
                if ($item->parent_i2) {
                    $children_2[] = $item;
                } elseif ($item->parent_i1) {
                    $children_1[] = $item;
                } else {
                    $temp = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'method' => $item->method,
                        'path' => $item->path,
                        'created_at' => $item->created_at,
                        'children' => [],
                    ];
                    if ($parent || $simple) {
                        unset($temp['method'], $temp['path'], $temp['created_at']);
                    }
                    $data[] = $temp;
                }
            }

            foreach ($data as $index => $item) {
                foreach ($children_1 as $value) {
                    if ($item['id'] == $value->parent_i1) {
                        $children_i1 = [
                            'id' => $value->id,
                            'parents' => [$value->parent_i1],
                            'name' => $value->name,
                            'slug' => $value->slug,
                            'method' => $value->method,
                            'path' => $value->path,
                            'created_at' => $value->created_at,
                            'children' => [],
                        ];

                        if (! $parent) {
                            foreach ($children_2 as $val) {
                                if ($children_i1['id'] == $val->parent_i2) {
                                    $children_i2 = [
                                        'id' => $val->id,
                                        'parents' => [$val->parent_i1, $val->parent_i2],
                                        'name' => $val->name,
                                        'slug' => $val->slug,
                                        'method' => $val->method,
                                        'path' => $val->path,
                                        'created_at' => $val->created_at,
                                    ];

                                    if ($simple) {
                                        unset($children_i2['parents'], $children_i2['method'], $children_i2['path'], $children_i2['created_at']);
                                    }

                                    $children_i1['children'][] = $children_i2;
                                }
                            }
                        }

                        if (! $children_i1['children']) unset($children_i1['children']);
                        else unset($children_i1['method'], $children_i1['path']);

                        if ($parent || $simple) {
                            unset($children_i1['parents'], $children_i1['method'], $children_i1['path'], $children_i1['created_at']);
                        }

                        $data[$index]['children'][] = $children_i1;
                        unset($value);
                    }
                }

                if (! $data[$index]['children']) unset($data[$index]['children']);
                else unset($data[$index]['method'], $data[$index]['path']);
            }
        }

        return $data;
    }

    /**
     * @param int $admin_id
     * @return Collection|AdminBindRole[]
     */
    public function toSelf(int $admin_id): Collection|array
    {
        return AdminBindRole::query()
            ->select(sprintf('%s.*', Permission::TABLE))
            ->leftJoin(Role::TABLE, function (JoinClause $query) {
                $query->on(sprintf('%s.role_id', AdminBindRole::TABLE), '=', sprintf('%s.id', Role::TABLE))
                    ->whereNull(sprintf('%s.deleted_at', Role::TABLE));
            })
            ->leftJoin(RoleBindPermission::TABLE, function (JoinClause $query) {
                $query->on(sprintf('%s.role_id', AdminBindRole::TABLE), '=', sprintf('%s.role_id', RoleBindPermission::TABLE))
                    ->whereNull(sprintf('%s.deleted_at', RoleBindPermission::TABLE));
            })
            ->leftJoin(Permission::TABLE, function (JoinClause $query) {
                $query->on(sprintf('%s.permission_id', RoleBindPermission::TABLE), '=', sprintf('%s.id', Permission::TABLE))
                    ->whereNull(sprintf('%s.deleted_at', Permission::TABLE));
            })
            ->whereNotNull(sprintf('%s.id', Permission::TABLE))
            ->where(sprintf('%s.admin_id', AdminBindRole::TABLE), $admin_id)
            ->whereNull(sprintf('%s.deleted_at', AdminBindRole::TABLE))
            ->get();
    }
}