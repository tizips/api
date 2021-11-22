<?php

declare(strict_types=1);

namespace App\Controller\Open;

use App\Constants\EnableConstants;
use App\Controller\AbstractController;
use App\Model\Link;
use App\Validator\Open\Link\ListValidator;
use Hyperf\Utils\Collection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class LinkController extends AbstractController
{
    /**
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function toList(): ResponseInterface
    {
        ListValidator::make();

        $data = [];

        $position = (int) $this->request->input('position');

        $builder = Link::query()
            ->where('is_enable', EnableConstants::IS_ENABLE_YES);

        if ($position > 0) $builder->where('position', $position);

        /** @var Link[]|Collection $links */
        $links = $builder->orderBy('no')
            ->get();

        if ($links->isNotEmpty()) {
            foreach ($links as $item) {
                $data[] = [
                    'name' => $item->name,
                    'uri' => $item->uri,
                    'logo' => $item->logo,
                ];
            }
        }

        return $this->response->apiSuccess($data);
    }
}