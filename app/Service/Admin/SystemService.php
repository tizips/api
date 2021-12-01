<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Model\System;
use App\Service\AbstractService;
use Hyperf\Utils\Collection;
use Psr\SimpleCache\InvalidArgumentException;

class SystemService extends AbstractService
{
    /**
     * @param string $type
     * @return array
     * @throws InvalidArgumentException
     */
    public function toListByCache(string $type): array
    {
        $data = (array) $this->cache->get($this->key($type));

        if (! $data) {
            /** @var System[]|Collection $systems */
            $systems = System::query()->where('type', $type)->get();

            if ($systems->isNotEmpty()) {
                foreach ($systems as $item) {
                    $data[$item->key] = $item->genre == System::GENRE_ENABLE ? intval($item->val) : $item->val;
                }
            }

            $this->cache->set($this->key($type), $data, config('databases.default.cache.ttl'));
        }

        return $data;
    }

    /**
     * @param string $type
     * @return bool
     * @throws InvalidArgumentException
     */
    public function doDeleteByCache(string $type): bool
    {
        return $this->cache->delete($this->key($type));
    }

    private function key(string $type): string
    {
        return sprintf('system:type:%s', $type);
    }
}