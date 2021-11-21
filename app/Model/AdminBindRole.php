<?php

declare (strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\SoftDeletes;

/**
 * @property int $id
 * @property int $admin_id
 * @property int $role_id
 */
class AdminBindRole extends Model
{
    use SoftDeletes;

    const TABLE = 'admin_bind_role';

    protected $table = self::TABLE;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'admin_id' => 'integer',
        'role_id' => 'integer',
    ];
}