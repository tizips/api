<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Enum\System\TypeEnum;
use App\Exception\ApiException;
use App\Helper\Admin;
use App\Model\System;
use App\Service\Admin\SystemService;
use App\Validator\Admin\System\doUpdateValidator;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Collection;
use HyperfExt\Enum\Exceptions\InvalidEnumMemberException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

class SystemController extends AbstractController
{
    #[Inject]
    private SystemService $SystemService;

    /**
     * @return ResponseInterface
     * @throws InvalidEnumMemberException
     */
    public function toList(): ResponseInterface
    {
        $data = [];

        /** @var System[]|Collection $systems */
        $systems = System::query()->get();

        if ($systems->isNotEmpty()) {
            $types = array_unique(array_column($systems->toArray(), 'type'));

            foreach ($types as $type) {

                $temp = [
                    'type' => $type,
                    'label' => TypeEnum::fromValue($type)->description,
                    'children' => [],
                ];

                foreach ($systems as $item) {
                    if ($item->type == $type) $temp['children'][] = [
                        'genre' => $item->genre,
                        'label' => $item->label,
                        'key' => $item->key,
                        'val' => $item->genre == System::GENRE_ENABLE ? intval($item->val) : $item->val,
                        'required' => $item->required,
                    ];
                }

                if ($temp['children']) $data[] = $temp;
            }
        }

        return $this->response->apiSuccess($data);
    }

    /**
     * @return ResponseInterface
     * @throws Throwable
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface|InvalidArgumentException
     */
    public function doUpdate(): ResponseInterface
    {
        doUpdateValidator::make();

        $type = (string) $this->request->input('type');

        $keys = [];

        foreach (Admin::KEYS[$type] as $item) {
            $keys[] = 'data.' . $item['key'];
        }

        $data = $this->request->inputs($keys);

        Db::beginTransaction();

        try {

            foreach ($data as $key => $item) {
                if (! is_null($item)) {
                    $k = substr($key, 5);
                    $affected = System::query()
                        ->where([
                            ['type', $type],
                            ['key', $k]
                        ])
                        ->update([
                            'val' => $item,
                        ]);
                    if ($affected <= 0) ApiException::break(sprintf('%s 修改失败！', $k));
                }
            }

            $this->SystemService->doDeleteByCache($type);

            Db::commit();
        } catch (Throwable $exception) {
            Db::rollBack();
            throw $exception;
        }

        return $this->response->apiSuccess();
    }
}