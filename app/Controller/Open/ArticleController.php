<?php

declare(strict_types=1);

namespace App\Controller\Open;

use App\Constants\EnableConstants;
use App\Controller\AbstractController;
use App\Exception\ApiException;
use App\Model\Article;
use App\Model\Category;
use App\Validator\Open\Article\toSearchValidator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class ArticleController extends AbstractController
{
    public function toPaginate(): ResponseInterface
    {
        $category_uri = (string) $this->request->input('uri');

        /** @var Category|null $category */
        $category = null;

        if ($category_uri) {

            /** @var Category $category */
            $category = Category::query()
                ->where([
                    ['uri', $category_uri],
                    ['is_enable', EnableConstants::IS_ENABLE_YES],
                ])
                ->first();

            if (! $category) ApiException::break('Not Found');
        }

        $builder = Article::query()
            ->with(['category', 'author'])
            ->where('is_enable', EnableConstants::IS_ENABLE_YES);

        if ($category) {

            $builder->where('category_id', $category->id);
        }

        $articles = $builder
            ->orderByDesc('id')
            ->paginate();

        $data = [];

        if ($articles->isNotEmpty()) {
            foreach ($articles->items() as $item) {
                /** @var Article $item */
                $data[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category->name,
                    'author' => $item->author->nickname,
                    'summary' => $item->summary,
                    'created_at' => $item->created_at->toDateTimeString(),
                ];
            }
        }

        return $this->response->apiPaginate($articles, $data);
    }

    public function toInformation(): ResponseInterface
    {
        $id = $this->request->route('id');

        /** @var Article $article */
        $article = Article::findFromCache($id);

        if (! $article) ApiException::break('Not Found');

        $data = [
            'id' => $article->id,
            'name' => $article->name,
            'category' => $article->category->name,
            'author_name' => $article->author->nickname,
            'author_avatar' => $article->author->avatar,
            'author_signature' => $article->author->signature,
            'picture' => $article->picture ?: $article->category->picture,
            'title' => $article->title,
            'keyword' => $article->keyword,
            'description' => $article->description,
            'source_name' => $article->source_name,
            'source_uri' => $article->source_uri,
            'content' => $article->content,
            'is_comment' => $article->is_comment,
            'created_at' => $article->created_at->toDateTimeString(),
        ];

        return $this->response->apiSuccess($data);
    }

    /**
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function toSearch(): ResponseInterface
    {
        toSearchValidator::make();

        $keyword = (string) $this->request->input('keyword');
        $size = (int) $this->request->input('size', 10);

        $articles = Article::search($keyword)
            ->with(['category', 'author'])
            ->paginate($size);

        $data = [];

        if ($articles->isNotEmpty()) {
            foreach ($articles->items() as $item) {
                /** @var Article $item */
                $data[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category->name,
                    'author' => $item->author->nickname,
                    'summary' => $item->summary,
                    'created_at' => $item->created_at,
                ];
            }
        }

        return $this->response->apiPaginate($articles, $data);
    }
}