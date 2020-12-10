<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Common\Api\Response;
use App\Controller\AbstractController;
use Hyperf\Utils\Context;
use Hyperf\Di\Annotation\Inject;

class AdminController extends AbstractController
{
    /**
     * @Inject
     * @var Response
     */
    protected $response;

    public function doAdminInformation()
    {
        $uid = Context::get('uid');

        $response = [
            'uid' => $uid,
        ];

        return $this->response->apiSuccess($response);
    }
}