<?php
declare(strict_types=1);

namespace App\Controller\Account;

use App\Common\Api\Response;
use App\Controller\AbstractController;
use Hyperf\Utils\Context;
use Hyperf\Di\Annotation\Inject;

class AccountController extends AbstractController
{
    /**
     * @Inject
     * @var Response
     */
    protected $response;

    public function doInformation(): \Psr\Http\Message\ResponseInterface
    {
        $uid = Context::get('uid');

        $response = [
            'uid' => $uid,
        ];

        return $this->response->apiSuccess($response);
    }
}