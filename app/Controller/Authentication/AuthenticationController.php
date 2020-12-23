<?php

declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Common\Api\Response;
use App\Controller\AbstractController;
use App\Request\Authentication\AccountRequest;
use App\Service\Authentication\AuthenticationService;
use Hyperf\Di\Annotation\Inject;

class AuthenticationController extends AbstractController
{
    /**
     * @Inject
     * @var Response
     */
    protected $response;

    /**
     * @Inject
     * @var AuthenticationService
     */
    private $adminService;

    public function doAccount(AccountRequest $request): \Psr\Http\Message\ResponseInterface
    {
        $username = (string)$request->input('username');
        $password = (string)$request->input('password');

        $response = $this->adminService->account($username, $password);

        return $this->response->apiSuccess($response);
    }
}
