<?php

declare(strict_types=1);

namespace App\Controller\Open;

use App\Controller\AbstractController;
use Psr\Http\Message\ResponseInterface;

class SiteController extends AbstractController
{
    public function toConfig(): ResponseInterface
    {
        $data = [
            'app_name' => config('app_name'),
            'app_close' => (bool) config('app_close'),
            'filing_icp' => config('filing_icp'),
            'filing_police' => config('filing_police'),
        ];

        return $this->response->apiSuccess($data);
    }
}