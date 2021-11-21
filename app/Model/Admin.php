<?php

declare (strict_types=1);

namespace App\Model;

use Carbon\Carbon;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Relations\HasManyThrough;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\ModelCache\Cacheable;

/**
 * @property int               $id
 * @property string            $username
 * @property string            $email
 * @property string            $mobile
 * @property string            $nickname
 * @property string            $avatar
 * @property string            $password
 * @property string            $signature
 * @property int               $is_enable
 * @property Carbon            $created_at
 * @property Carbon            $updated_at
 *
 * @property Role[]|Collection $roles
 */
class Admin extends Model
{
    use Cacheable;
    use SoftDeletes;

    const TABLE = 'admin';

    protected $table = self::TABLE;

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'is_enable' => 'integer',
    ];

    public function roles(): HasManyThrough
    {
        return $this->hasManyThrough(Role::class, AdminBindRole::class, 'admin_id', 'id', 'id', 'role_id');
    }
}