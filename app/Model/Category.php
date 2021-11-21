<?php

declare (strict_types=1);

namespace App\Model;

use Carbon\Carbon;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Hyperf\Database\Model\SoftDeletes;

/**
 * @property int                  $id
 * @property int                  $parent_id
 * @property string               $name
 * @property string               $picture
 * @property string               $title
 * @property string               $keyword
 * @property string               $description
 * @property string               $uri
 * @property int                  $no
 * @property int                  $is_page
 * @property int                  $is_comment
 * @property int                  $is_enable
 * @property string               $page
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 *
 * @property Category             $parent
 * @property Category             $child
 * @property Article[]|Collection $articles
 */
class Category extends Model
{
    use SoftDeletes;

    const TABLE = 'category';

    protected $table = self::TABLE;

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'parent_id' => 'integer',
        'no' => 'integer',
        'is_page' => 'integer',
        'is_comment' => 'integer',
        'is_enable' => 'integer',
    ];

    const IS_PAGE_YES = 1;
    const IS_PAGE_NO = 0;

    const IS_COMMENT_YES = 1;
    const IS_COMMENT_NO = 0;

    public function parent(): HasOne
    {
        return $this->hasOne(Category::class, 'id', 'parent_id');
    }

    public function child(): HasOne
    {
        return $this->hasOne(Category::class, 'parent_id', 'id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'category_id', 'id');
    }
}