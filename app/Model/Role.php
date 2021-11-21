<?php

declare (strict_types=1);

namespace App\Model;

use Carbon\Carbon;
use Hyperf\Database\Model\Relations\HasManyThrough;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\Utils\Collection;

/**
 * @property int                     $id
 * @property string                  $slug
 * @property string                  $name
 * @property string                  $summary
 * @property Carbon                  $created_at
 * @property Carbon                  $updated_at
 *
 * @property Permission[]|Collection $permissions
 */
class Role extends Model
{
    use SoftDeletes;

    const TABLE = 'role';

    protected $table = self::TABLE;

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
    ];

    public function permissions(): HasManyThrough
    {
        return $this->hasManyThrough(Permission::class, RoleBindPermission::class, 'role_id', 'id', 'id', 'permission_id');
    }
}