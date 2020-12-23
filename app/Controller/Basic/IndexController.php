<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Controller\Basic;

use App\Common\Api\Response;
use App\Controller\AbstractController;
use Hyperf\Di\Annotation\Inject;

class IndexController extends AbstractController
{
    /**
     * @Inject
     * @var Response
     */
    protected $response;

    public function index(): \Psr\Http\Message\ResponseInterface
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        return $this->response->apiSuccess(
            [
                'name' => 'The Service Of Api',
                'method' => $method,
                'message' => "Hello {$user}.",
            ]
        );
    }

    public function init(): array
    {
        return [
            'username' => 'admin',
            'password' => '123456',
            'password_encryption' => password_hash('123456', PASSWORD_BCRYPT),
        ];
    }
}
