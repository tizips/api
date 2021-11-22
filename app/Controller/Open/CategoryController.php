<?php

declare(strict_types=1);

namespace App\Controller\Open;

use App\Constants\EnableConstants;
use App\Controller\AbstractController;
use App\Exception\ApiException;
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

    public function toInformation(): ResponseInterface
    {
        $uri = $this->request->route('uri');

        /** @var Category $category */
        $category = Category::query()
            ->where('uri', $uri)
            ->where('is_enable', EnableConstants::IS_ENABLE_YES)
            ->first();

        if (! $category) ApiException::break('Not Found', 40400);

        $data = [
            'id' => $category->id,
            'name' => $category->name,
            'picture' => $category->picture,
            'title' => $category->title,
            'keyword' => $category->keyword,
            'description' => $category->description,
            'is_page' => $category->is_page,
            'is_comment' => $category->is_comment,
            'page' => $category->page,
            'created_at' => $category->created_at->toDateTimeString(),
        ];

        return $this->response->apiSuccess($data);
    }
}