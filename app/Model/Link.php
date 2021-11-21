<?php

declare (strict_types=1);

namespace App\Model;

use Carbon\Carbon;
use Hyperf\Database\Model\SoftDeletes;

/**
 * @property int    $id
 * @property string $name
 * @property string $uri
 * @property string $logo
 * @property string $admin
 * @property string $email
 * @property string $summary
 * @property int    $no
 * @property int    $position
 * @property int    $is_enable
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Link extends Model
{
    use SoftDeletes;

    const TABLE = 'link';

    protected $table = self::TABLE;

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'no' => 'integer',
        'position' => 'integer',
        'is_enable' => 'integer',
    ];

    const POSITION_ALL = 0;
    const POSITION_BOTTOM = 1;
    const POSITION_OTHER = 2;
}