<?php

declare (strict_types=1);

namespace App\Model;

use Carbon\Carbon;
use Hyperf\Database\Model\Relations\HasOne;
use Hyperf\Database\Model\SoftDeletes;

/**
 * @property int        $id
 * @property int        $parent_i1
 * @property int        $parent_i2
 * @property string     $name
 * @property string     $slug
 * @property string     $method
 * @property string     $path
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 *
 * @property Permission $parent1
 * @property Permission $parent2
 */
class Permission extends Model
{
    use SoftDeletes;

    const TABLE = 'permission';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    public function parent1(): HasOne
    {
        return $this->hasOne(Permission::class, 'id', 'parent_i1');
    }

    public function parent2(): HasOne
    {
        return $this->hasOne(Permission::class, 'id', 'parent_i2');
    }
}