<?php

declare(strict_types=1);

namespace App\Controller\Open;

use App\Constants\EnableConstants;
use App\Controller\AbstractController;
use App\Model\Category;
use Hyperf\Utils\Collection;
use Psr\Http\Message\ResponseInterface;

class CategoryController extends AbstractController
{
    public function toTree(): ResponseInterface
    {
        $data = [];

        /** @var Category[]|Collection $categories */
        $categories = Category::query()
            ->select(['id', 'parent_id', 'name', 'picture', 'uri', 'is_page'])
            ->where('is_enable', EnableConstants::IS_ENABLE_YES)
            ->orderBy('no')
            ->get();

        if ($categories->isNotEmpty()) {
            foreach ($categories as $item) {
                if ($item->parent_id <= 0) {
                    $category = array_merge($item->toArray(), ['children' => []]);
                    foreach ($categories as $value) {
                        if ($item->id == $value->parent_id) {
                            $category['children'][] = $value;
                        }
                    }
                    if (! $category['children']) unset($category['children']);
                    $data[] = $category;
                }
            }
        }

        return $this->response->apiSuccess($data);
    }
}