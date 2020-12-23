<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\Api\Status;
use App\Exception\ApiException;
use App\Model\Admin;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Di\Annotation\Inject;

class AdminRepository
{
    /**
     * @Inject
     * @var Admin
     */
    protected $admin;

    public function getAdminByUsername(string $username, int $ttl = 0, array $params = ['*']): array
    {
        $redis = $key = null;

        if ($ttl > 0) {
            $redis = ApplicationContext::getContainer()->get(Redis::class);
            $key = $this->admin::TABLE . ':username:' . $username;
        }

        if ($ttl === 0 || !$redis->exists($key)) {
            $admin = $this->admin::query()->select($params)->where('username', $username)->first();

            if (!empty($admin) && $ttl >= 0) {
                $ok = $redis->hMSet($key, $admin->toArray());
                if ($ok) {
                    $redis->expire($key, 180);
                }
            }
        } else {
            $admin = $redis->hGetAll($key);
        }

        return $admin;
    }

    public function cacheAdminById($id, array $admin, float $ttl = 0)
    {
        $redis = ApplicationContext::getContainer()->get(Redis::class);
        $key = $this->admin::TABLE . ':id:' . $id;

        if (!$redis->exists($key)) {
            $ok = $redis->hMSet($key, $admin);

            if (!$ok) {
                ApiException::break(Status::ERR_SYS);
            }
        }

        if ($ttl == 0) {
            $redis->persist($key);
        } elseif ($ttl > 0) {
            $redis->expire($key, $ttl);
        } else {
            $redis->del($key);
        }
    }
}
