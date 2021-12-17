<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Constants\EnableConstants;
use App\Controller\AbstractController;
use App\Exception\ApiException;
use App\Kernel\Admin\Auth;
use App\Model\Article;
use App\Model\Category;
use App\Service\Utils\MeilisearchService;
use App\Validator\Admin\Article\doCreateValidator;
use App\Validator\Admin\Article\doUpdateValidator;
use App\Validator\Unit\doEnableValidator;
use Exception;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class ArticleController extends AbstractController
{
    #[Inject]
    private MeilisearchService $MeilisearchService;

    /**
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function doCreate(): ResponseInterface
    {
        doCreateValidator::make();

        $category_id = (int) $this->request->input('category');
        $name = (string) $this->request->input('name');
        $picture = (string) $this->request->input('picture');
        $title = (string) $this->request->input('title');
        $keyword = (string) $this->request->input('keyword');
        $description = (string) $this->request->input('description');
        $source_name = (string) $this->request->input('source_name');
        $source_uri = (string) $this->request->input('source_uri');
        $is_comment = (int) $this->request->input('is_comment');
        $is_enable = (int) $this->request->input('is_enable');
        $content = (string) $this->request->input('content');

        /** @var Category $category */
        $category = Category::query()->where('id', $category_id)->first();
        if (! $category) ApiException::break('栏目不存在！');
        elseif ($category->is_page === Category::IS_PAGE_YES) ApiException::break('非列表栏目，无法添加！');
        elseif ($category->child) ApiException::break('栏目非终极栏目，无法添加！');

        /** @var Article $article */
        $article = Article::query()->create([
            'category_id' => $category->id,
            'name' => $name,
            'picture' => $picture,
            'title' => $title,
            'keyword' => $keyword,
            'description' => $description,
            'admin_id' => Auth::id(),
            'source_name' => $source_name,
            'source_uri' => $source_uri,
            'is_comment' => $is_comment,
            'is_enable' => $is_enable,
            'summary' => Str::limit(strip_tags($content), 255),
            'content' => $content,
        ]);

        if (! $article) ApiException::break('文章添加失败！');

        $this->MeilisearchService->save($article);

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
        $category_id = (int) $this->request->input('category');
        $name = (string) $this->request->input('name');
        $picture = (string) $this->request->input('picture');
        $title = (string) $this->request->input('title');
        $keyword = (string) $this->request->input('keyword');
        $description = (string) $this->request->input('description');
        $source_name = (string) $this->request->input('source_name');
        $source_uri = (string) $this->request->input('source_uri');
        $is_comment = (int) $this->request->input('is_comment');
        $is_enable = (int) $this->request->input('is_enable');
        $content = (string) $this->request->input('content');

        $article = Article::query()->where('id', $id)->first();
        if (! $article) ApiException::break('文章不存在！');

        /** @var Category $category */
        $category = Category::query()->where('id', $category_id)->first();

        if (! $category) ApiException::break('栏目不存在！');
        elseif ($category->is_page === Category::IS_PAGE_YES) ApiException::break('非列表栏目，无法修改！');
        elseif ($category->child) ApiException::break('栏目非终极栏目，无法修改！');

        $affected = $article
            ->update([
                'category_id' => $category->id,
                'name' => $name,
                'picture' => $picture,
                'title' => $title,
                'keyword' => $keyword,
                'description' => $description,
                'source_name' => $source_name,
                'source_uri' => $source_uri,
                'is_comment' => $is_comment,
                'is_enable' => $is_enable,
                'summary' => Str::limit(strip_tags($content), 255),
                'content' => $content,
            ]);

        if ($affected <= 0) ApiException::break('文章修改失败！');

        /** @var Article $article */
        $article = $article->refresh();

        $this->MeilisearchService->save($article);

        return $this->response->apiSuccess();
    }

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function doDelete(): ResponseInterface
    {
        $id = $this->request->route('id');

        /** @var Article $category */
        $article = Article::query()->where('id', $id)->first();
        if (! $article) ApiException::break('文章不存在！');

        $affected = $article->delete();
        if ($affected <= 0) ApiException::break('文章删除失败！');

        $this->MeilisearchService->delete($id);

        return $this->response->apiSuccess();
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

        $article = Article::query()->where('id', $id)->first();
        if (! $article) ApiException::break('文章不存在！');

        $affected = $article->update(['is_enable' => $enable]);
        if ($affected <= 0) ApiException::break(sprintf('文章%s失败！', $enable == EnableConstants::IS_ENABLE_YES ? '启用' : '禁用'));

        /** @var Article $article */
        $article = $article->refresh();

        $this->MeilisearchService->save($article);

        return $this->response->apiSuccess();
    }

    public function toInformation(): ResponseInterface
    {
        $id = (int) $this->request->route('id');

        /** @var Article $article */
        $article = Article::query()->where('id', $id)->first();
        if (! $article) ApiException::break('文章不存在！');

        $data = [
            'id' => $article->id,
            'category' => [$article->category_id],
            'name' => $article->name,
            'picture' => $article->picture,
            'title' => $article->title,
            'keyword' => $article->keyword,
            'description' => $article->description,
            'source_name' => $article->source_name,
            'source_uri' => $article->source_uri,
            'is_comment' => $article->is_comment,
            'is_enable' => $article->is_enable,
            'content' => $article->content,
            'created_at' => $article->created_at,
        ];

        if ($article->category && $article->category->parent_id > 0) {
            array_unshift($data['category'], $article->category->parent_id);
        }

        return $this->response->apiSuccess($data);
    }

    public function toPaginate(): ResponseInterface
    {
        $data = [];

        $articles = Article::query()->with(['category', 'author'])->orderByDesc('id')->paginate();

        if ($articles->isNotEmpty()) {
            foreach ($articles->items() as $item) {
                /** @var Article $item */
                $data[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category->name,
                    'author' => $item->author->nickname,
                    'is_enable' => $item->is_enable,
                    'created_at' => $item->created_at,
                ];
            }
        }

        return $this->response->apiPaginate($articles, $data);
    }
}