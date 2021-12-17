<?php

declare(strict_types=1);

namespace App\Validator\Admin\Login;

use App\Helper\Pattern;
use App\Validator\Validator;

class doLoginValidator extends Validator
{
    protected function rule(): array
    {
        return [
            'username' => [
                'required', 'between:4,20', 'regex:' . Pattern::ADMIN_USERNAME,
            ],
            'password' => [
                'required', 'between:4,20', 'regex:' . Pattern::ADMIN_PASSWORD,
            ],
        ];
    }
}