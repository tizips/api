<?php

declare(strict_types=1);

namespace App\Service\Utils;

use App\Constants\EnableConstants;
use App\Model\Article;
use App\Service\AbstractService;
use MeiliSearch\Client;
use MeiliSearch\Search\SearchResult;

class MeilisearchService extends AbstractService
{
    private string $url;

    private string $key;

    public function __construct()
    {
        $this->url = sprintf('%s:%d', config('meilisearch.url'), config('meilisearch.port'));

        $this->key = config('meilisearch.key');
    }

    public function search($keyword, int $page = 1, int $size = 20): array|SearchResult
    {
        $index = $this->client()->index(Article::TABLE);

        $offset = (max($page, 1) - 1) * $size;

        return $index->search($keyword, [
            'filter' => sprintf('is_enable=%d', EnableConstants::IS_ENABLE_YES),
            'offset' => $offset,
            'limit' => $size,
        ]);
    }

    public function filter()
    {
        $index = $this->client()->index(Article::TABLE);

        $index->updateFilterableAttributes(['is_enable']);
    }

    public function save(Article $article)
    {
        $index = $this->client()->index(Article::TABLE);

        $index->addDocuments([$article->toArray()], 'id');
    }

    public function delete(int $id)
    {
        $index = $this->client()->index(Article::TABLE);

        $index->deleteDocument($id);
    }

    public function client(): Client
    {
        return new Client($this->url, $this->key);
    }
}