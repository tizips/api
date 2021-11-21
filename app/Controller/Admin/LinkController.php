<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Constants\EnableConstants;
use App\Controller\AbstractController;
use App\Exception\ApiException;
use App\Model\Link;
use App\Validator\Admin\Link\CreateValidator;
use App\Validator\Admin\Link\UpdateValidator;
use App\Validator\Unit\EnableValidator;
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
    public function doCreate(): ResponseInterface
    {
        CreateValidator::make();

        $name = (string) $this->request->input('name');
        $uri = (string) $this->request->input('uri');
        $no = (int) $this->request->input('no');
        $is_enable = (int) $this->request->input('is_enable');
        $position = (int) $this->request->input('position');
        $summary = (string) $this->request->input('summary');
        $logo = (string) $this->request->input('logo');
        $admin = (string) $this->request->input('admin');
        $email = (string) $this->request->input('email');

        $link = Link::query()
            ->create([
                'name' => $name,
                'uri' => $uri,
                'admin' => $admin,
                'email' => $email,
                'logo' => $logo,
                'summary' => $summary,
                'no' => $no,
                'position' => $position,
                'is_enable' => $is_enable,
            ]);

        if (! $link) ApiException::break('友链添加失败！');

        return $this->response->apiSuccess();
    }

    /**
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function doUpdate(): ResponseInterface
    {
        UpdateValidator::make();

        $id = (int) $this->request->route('id');
        $name = (string) $this->request->input('name');
        $uri = (string) $this->request->input('uri');
        $no = (int) $this->request->input('no');
        $is_enable = (int) $this->request->input('is_enable');
        $position = (int) $this->request->input('position');
        $summary = (string) $this->request->input('summary');
        $logo = (string) $this->request->input('logo');
        $admin = (string) $this->request->input('admin');
        $email = (string) $this->request->input('email');

        $link = Link::query()->where('id', $id)->first();
        if (! $link) ApiException::break('友链不存在！');

        $affected = Link::query()
            ->where('id', $id)
            ->update([
                'name' => $name,
                'uri' => $uri,
                'admin' => $admin,
                'email' => $email,
                'logo' => $logo,
                'summary' => $summary,
                'no' => $no,
                'position' => $position,
                'is_enable' => $is_enable,
            ]);

        if ($affected <= 0) ApiException::break('友链修改失败！');

        return $this->response->apiSuccess();
    }

    /**
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function doEnable(): ResponseInterface
    {
        EnableValidator::make();

        $id = (int) $this->request->input('id');
        $enable = (int) $this->request->input('enable');

        $link = Link::query()->where('id', $id)->first();
        if (! $link) ApiException::break('友链不存在！');

        $affected = Link::query()->where('id', $id)->update(['is_enable' => $enable]);
        if ($affected <= 0) ApiException::break(sprintf('友链%s失败！', $enable == EnableConstants::IS_ENABLE_YES ? '启用' : '禁用'));

        return $this->response->apiSuccess();
    }

    public function doDelete(): ResponseInterface
    {
        $id = $this->request->route('id');

        /** @var Link $link */
        $link = Link::query()->where('id', $id)->first();
        if (! $link) ApiException::break('友链不存在！');

        $affected = Link::query()->where('id', $id)->delete();
        if ($affected <= 0) ApiException::break('友链删除失败！');

        return $this->response->apiSuccess();
    }

    public function toPaginate(): ResponseInterface
    {
        $data = [];

        $links = Link::query()->paginate();

        if ($links->isNotEmpty()) {
            foreach ($links as $item) {
                /** @var Link $item */
                $data[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'uri' => $item->uri,
                    'logo' => $item->logo,
                    'admin' => $item->admin,
                    'email' => $item->email,
                    'summary' => $item->summary,
                    'no' => $item->no,
                    'position' => $item->position,
                    'is_enable' => $item->is_enable,
                    'created_at' => $item->created_at,
                ];
            }
        }

        return $this->response->apiPaginate($links, $data);
    }
}