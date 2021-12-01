<?php

declare (strict_types=1);

namespace App\Model;

use Carbon\Carbon;
use Hyperf\Database\Model\SoftDeletes;

/**
 * @property int    $id
 * @property string $type
 * @property string $genre
 * @property string $label
 * @property string $key
 * @property string $val
 * @property int    $required
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class System extends Model
{
    use SoftDeletes;

    const TABLE = 'system';

    protected $table = self::TABLE;

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'key' => 'string',
        'val' => 'string',
        'required' => 'integer',
    ];

    const GENRE_INPUT = 'input';
    const GENRE_TEXTAREA = 'textarea';
    const GENRE_ENABLE = 'enable';
    const GENRE_URL = 'url';
    const GENRE_EMAIL = 'email';

    //  必填：0=否；1=是
    const REQUIRED_YES = 1;
    const REQUIRED_NO = 0;
}