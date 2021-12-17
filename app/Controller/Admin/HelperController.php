<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Service\Admin\HelperService;
use App\Validator\Admin\Helper\doUploadValidator;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Snowflake\IdGeneratorInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class HelperController extends AbstractController
{
    #[Inject]
    protected Filesystem $filesystem;

    #[Inject]
    private HelperService $HelperService;

    /**
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function toApis(): ResponseInterface
    {
        $data = [];

        $apis = $this->HelperService->toApis();

        if ($apis) {
            foreach ($apis as $item) {
                if (str_starts_with($item['path'], '/admin')) $data[] = $item;
            }
        }

        return $this->response->apiSuccess($data);
    }

    /**
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function doUpload(): ResponseInterface
    {
        doUploadValidator::make(true);

        $file = $this->request->file('file');
        $dir = (string) $this->request->input('dir', '');

        $generator = $this->container->get(IdGeneratorInterface::class);

        //  根据文件内容生成文件名称
        $name = $generator->generate() . '.' . $file->getExtension();

        $path = $dir . '/' . $name;

        $stream = fopen($file->getRealPath(), 'r+');

        try {
            $this->filesystem->writeStream($path, $stream);
        } catch (FilesystemException $exception) {
        }

        if (is_resource($stream)) fclose($stream);

        $domain = config('api') . '/upload';

        if (config('file.default') == 'qiniu') {
            $domain = config('file.storage.qiniu.domain');
        }

        $url = $domain . $path;

        return $this->response->apiSuccess([
            'name' => $file->getClientFilename(),
            'url' => $url,
        ]);
    }
}