<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Constants\EnableConstants;
use App\Controller\AbstractController;
use App\Exception\ApiException;
use App\Model\Category;
use App\Validator\Admin\Category\doCreateValidator;
use App\Validator\Admin\Category\doUpdateValidator;
use App\Validator\Unit\doEnableValidator;
use Hyperf\Utils\Collection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class CategoryController extends AbstractController
{
    /**
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function doCreate(): ResponseInterface
    {
        doCreateValidator::make();

        $parent_id = (int) $this->request->input('parent');
        $name = (string) $this->request->input('name');
        $picture = (string) $this->request->input('picture');
        $title = (string) $this->request->input('title');
        $keyword = (string) $this->request->input('keyword');
        $description = (string) $this->request->input('description');
        $uri = (string) $this->request->input('uri');
        $no = (int) $this->request->input('no', 50);
        $is_page = (int) $this->request->input('is_page');
        $is_comment = (int) $this->request->input('is_comment');
        $is_enable = (int) $this->request->input('is_enable');
        $page = (string) $this->request->input('page');

        if ($parent_id > 0) {
            /** @var Category $parent */
            $parent = Category::query()->where('id', $parent_id)->first();

            if (! $parent) ApiException::break('未找到该父级栏目！');
            if ($parent->parent_id > 0) ApiException::break('该栏目已是最低层级！');
            if ($parent->is_page == Category::IS_PAGE_YES) ApiException::break('单页面无法添加下级');
        }

        $create = [
            'parent_id' => $parent_id,
            'name' => $name,
            'picture' => $picture,
            'title' => $title,
            'keyword' => $keyword,
            'description' => $description,
            'uri' => $uri,
            'no' => $no,
            'is_page' => $is_page,
            'is_comment' => $is_comment,
            'is_enable' => $is_enable,
        ];

        if ($is_page == Category::IS_PAGE_YES && $page) $create['page'] = $page;

        $category = Category::query()->create($create);

        if (! $category) ApiException::break('栏目添加失败！');

        return $this->response->apiSuccess();
    }

    /**
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function doUpdate(): ResponseInterface
    {
        doUpdateValidator::make();

        $id = (int) $this->request->route('id');
        $parent_id = (int) $this->request->input('parent');
        $name = (string) $this->request->input('name');
        $picture = (string) $this->request->input('picture');
        $title = (string) $this->request->input('title');
        $keyword = (string) $this->request->input('keyword');
        $description = (string) $this->request->input('description');
        $uri = (string) $this->request->input('uri');
        $no = (int) $this->request->input('no', 50);
        $is_page = (int) $this->request->input('is_page');
        $is_comment = (int) $this->request->input('is_comment');
        $is_enable = (int) $this->request->input('is_enable');
        $page = (string) $this->request->input('page');

        if ($parent_id > 0) {
            /** @var Category $parent */
            $parent = Category::query()->where('id', $parent_id)->first();

            if (! $parent) ApiException::break('未找到该父级栏目！');
            if ($parent->parent_id > 0) ApiException::break('该栏目已是最低层级！');
            if ($parent->is_page == Category::IS_PAGE_YES) ApiException::break('单页面无法添加下级');
        }

        /** @var Category $category */
        $category = Category::query()->where('id', $id)->first();
        if (! $category) ApiException::break('栏目不存在！');

        if (empty($uri) && $uri != $category->uri && $category->articles->count() > 0) {
            ApiException::break('该栏目下已有文章，无法再次修改类型！');
        }

        $update = [
            'parent_id' => $parent_id,
            'name' => $name,
            'picture' => $picture,
            'title' => $title,
            'keyword' => $keyword,
            'description' => $description,
            'uri' => $uri,
            'no' => $no,
            'is_page' => $is_page,
            'is_comment' => $is_comment,
            'is_enable' => $is_enable,
        ];

        if ($is_page == Category::IS_PAGE_YES && $page) $update['page'] = $page;

        $affected = Category::query()->where('id', $id)->update($update);

        if ($affected <= 0) ApiException::break('栏目修改失败！');

        return $this->response->apiSuccess();
    }

    public function doDelete(): ResponseInterface
    {
        $id = $this->request->route('id');

        /** @var Category $category */
        $category = Category::query()->where('id', $id)->first();
        if (! $category) ApiException::break('栏目不存在！');

        if ($category->parent_id <= 0 && $category->child) ApiException::break('该栏目存在子栏目，无法直接删除！');

        $affected = Category::query()->where('id', $id)->delete();
        if ($affected <= 0) ApiException::break('栏目删除失败！');

        return $this->response->apiSuccess();
    }

    public function toInformation(): ResponseInterface
    {
        $id = $this->request->route('id');

        /** @var Category $category */
        $category = Category::query()->where('id', $id)->first();
        if (! $category) ApiException::break('栏目不存在！');

        $data = [
            'id' => $category->id,
            'parent' => $category->parent_id,
            'name' => $category->name,
            'picture' => $category->picture,
            'title' => $category->title,
            'keyword' => $category->keyword,
            'description' => $category->description,
            'uri' => $category->uri,
            'no' => $category->no,
            'is_page' => $category->is_page,
            'is_comment' => $category->is_comment,
            'is_enable' => $category->is_enable,
            'page' => $category->page,
            'created_at' => $category->created_at,
        ];

        return $this->response->apiSuccess($data);
    }

    public function toTree(): ResponseInterface
    {
        $data = [];

        /** @var Category[]|Collection $categories */
        $categories = Category::query()->get();

        if ($categories->isNotEmpty()) {
            foreach ($categories as $index => $item) {
                if ($item->parent_id <= 0) {
                    $parents = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'uri' => $item->uri,
                        'no' => $item->no,
                        'is_page' => $item->is_page,
                        'is_comment' => $item->is_comment,
                        'is_enable' => $item->is_enable,
                        'created_at' => $item->created_at,
                        'children' => [],
                    ];

                    unset($categories[$index]);

                    foreach ($categories as $key => $value) {
                        if ($item->id == $value->parent_id) {
                            $parents['children'][] = [
                                'id' => $value->id,
                                'name' => $value->name,
                                'uri' => $value->uri,
                                'no' => $value->no,
                                'is_page' => $value->is_page,
                                'is_comment' => $value->is_comment,
                                'is_enable' => $value->is_enable,
                                'created_at' => $value->created_at,
                            ];
                            unset($categories[$key]);
                        }
                    }

                    if (! $parents['children']) unset($parents['children']);

                    $data[] = $parents;
                }
            }
        }

        return $this->response->apiSuccess($data);
    }

    public function toUsed(): ResponseInterface
    {
        $data = [];

        /** @var Category[]|Collection $categories */
        $categories = Category::query()
            ->where([
                ['is_page', Category::IS_PAGE_NO],
                ['is_enable', EnableConstants::IS_ENABLE_YES],
            ])
            ->get();

        if ($categories->isNotEmpty()) {
            foreach ($categories as $index => $item) {
                if ($item->parent_id <= 0) {
                    $parents = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'is_enable' => $item->is_enable,
                        'children' => [],
                    ];

                    unset($categories[$index]);

                    foreach ($categories as $key => $value) {
                        if ($item->id == $value->parent_id) {
                            $parents['children'][] = [
                                'id' => $value->id,
                                'name' => $value->name,
                                'is_enable' => $item->is_enable,
                            ];
                            unset($categories[$key]);
                        }
                    }

                    if (! $parents['children']) unset($parents['children']);

                    $data[] = $parents;
                }
            }
        }

        return $this->response->apiSuccess($data);
    }

    public function toParents(): ResponseInterface
    {
        $data = [];

        /** @var Category[]|Collection $categories */
        $categories = Category::query()
            ->select(['id', 'name'])
            ->where([
                ['parent_id', 0],
                ['uri', ''],
                ['is_page', Category::IS_PAGE_NO]
            ])
            ->orderBy('no')
            ->get();

        if ($categories->isNotEmpty()) {
            foreach ($categories as $item) {
                $data[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                ];
            }
        }

        return $this->response->apiSuccess($data);
    }

    /**
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function doEnable(): ResponseInterface
    {
        doEnableValidator::make();

        $id = (int) $this->request->input('id');
        $enable = (int) $this->request->input('enable');

        $category = Category::query()->where('id', $id)->first();
        if (! $category) ApiException::break('栏目不存在！');

        $affected = Category::query()->where('id', $id)->update(['is_enable' => $enable]);
        if ($affected <= 0) ApiException::break(sprintf('栏目%s失败！', $enable == EnableConstants::IS_ENABLE_YES ? '启用' : '禁用'));

        return $this->response->apiSuccess();
    }
}