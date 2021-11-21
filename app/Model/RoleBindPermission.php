<?php

declare (strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\SoftDeletes;

/**
 * @property int $id
 * @property int $role_id
 * @property int $permission_id
 */
class RoleBindPermission extends Model
{
    use SoftDeletes;

    const TABLE = 'role_bind_permission';

    protected $table = self::TABLE;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'role_id' => 'integer',
        'permission_id' => 'integer',
    ];
}