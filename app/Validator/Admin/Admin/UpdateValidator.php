<?php

declare(strict_types=1);

namespace App\Validator\Admin\Admin;

use App\Helper\Pattern;
use App\Validator\Validator;

class UpdateValidator extends Validator
{
    protected function rule(): array
    {
        return [
            'nickname' => [
                'required', 'max:20',
            ],
            'password' => [
                'nullable', 'max:20', 'regex:' . Pattern::ADMIN_PASSWORD,
            ],
            'roles' => [
                'required', 'array',
            ],
            'roles.*' => [
                'required', 'integer', 'gt:0',
            ],
            'signature' => [
                'nullable', 'max:255',
            ],
        ];
    }
}