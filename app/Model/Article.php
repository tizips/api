<?php

declare (strict_types=1);

namespace App\Model;

use App\Constants\EnableConstants;
use Carbon\Carbon;
use Hyperf\Database\Model\Relations\HasOne;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\ModelCache\Cacheable;
use HyperfExt\Scout\Searchable;

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
    use Searchable;
    use SoftDeletes;
    use Cacheable;

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

    public function getScoutSettings(): ?array
    {
        return [
            'analysis' => [
                'analyzer' => [
                    'ik_pinyin_analyzer' => [
                        'type' => 'custom',
                        'tokenizer' => 'ik_max_word',
                        'filter' => [
                            'up_pinyin',
                        ],
                    ],
                ],
                'filter' => [
                    'up_pinyin' => [
                        'type' => 'pinyin',
                    ],
                ],
            ],
        ];
    }

    public function getScoutMapping(): array
    {
        return [
            'properties' => [
                'id' => ['type' => 'integer'],
                'category_id' => ['type' => 'integer'],
                'name' => ['type' => 'text', 'analyzer' => 'ik_pinyin_analyzer'],
                'picture' => ['type' => 'keyword'],
                'title' => ['type' => 'text', 'analyzer' => 'ik_pinyin_analyzer'],
                'keyword' => ['type' => 'text', 'analyzer' => 'ik_pinyin_analyzer'],
                'description' => ['type' => 'text', 'analyzer' => 'ik_pinyin_analyzer'],
                'admin_id' => ['type' => 'integer'],
                'source_name' => ['type' => 'keyword'],
                'source_uri' => ['type' => 'keyword'],
                'summary' => ['type' => 'text', 'analyzer' => 'ik_pinyin_analyzer'],
                'content' => ['type' => 'text', 'analyzer' => 'ik_pinyin_analyzer'],
                'is_comment' => ['type' => 'byte'],
                'is_enable' => ['type' => 'byte'],
                'created_at' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss||date_optional_time||epoch_millis'],
                'updated_at' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss||date_optional_time||epoch_millis'],
            ],
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->is_enable == EnableConstants::IS_ENABLE_YES;
    }
}