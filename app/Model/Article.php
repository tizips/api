<?php

declare (strict_types=1);

namespace App\Model;

use Carbon\Carbon;
use Hyperf\Database\Model\Relations\HasOne;
use Hyperf\Database\Model\SoftDeletes;

/**
 * @property int      $id
 * @property int      $category_id
 * @property string   $name
 * @property string   $picture
 * @property string   $title
 * @property string   $keyword
 * @property string   $description
 * @property int      $admin_id
 * @property string   $source_name
 * @property string   $source_uri
 * @property int      $is_comment
 * @property int      $is_enable
 * @property string   $summary
 * @property string   $content
 * @property Carbon   $created_at
 * @property Carbon   $updated_at
 *
 * @property Category $category
 * @property Admin    $author
 */
class Article extends Model
{
    use SoftDeletes;

    const TABLE = 'article';

    protected $table = self::TABLE;

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'category_id' => 'integer',
        'admin_id' => 'integer',
        'is_comment' => 'integer',
        'is_enable' => 'integer',
    ];

    public function category(): HasOne
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    public function author(): HasOne
    {
        return $this->hasOne(Admin::class, 'id', 'admin_id');
    }
}