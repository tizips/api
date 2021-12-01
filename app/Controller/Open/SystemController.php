<?php

declare(strict_types=1);

namespace App\Controller\Open;

use App\Controller\AbstractController;
use App\Enum\System\TypeEnum;
use App\Service\Admin\SystemService;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\InvalidArgumentException;

class SystemController extends AbstractController
{
    #[Inject]
    private SystemService $SystemService;

    /**
     * @return ResponseInterface
     * @throws InvalidArgumentException
     */
    public function toSite(): ResponseInterface
    {
        $data = $this->SystemService->toListByCache(TypeEnum::SITE);

        return $this->response->apiSuccess($data);
    }
}