<?php

declare(strict_types=1);

namespace App\Validator\Admin\Admin;

use App\Helper\Pattern;
use App\Validator\Validator;

class doCreateValidator extends Validator
{
    protected function rule(): array
    {
        return [
            'username' => [
                'required', 'max:20', 'regex:' . Pattern::ADMIN_USERNAME,
            ],
            'nickname' => [
                'required', 'max:20',
            ],
            'password' => [
                'required', 'max:20', 'regex:' . Pattern::ADMIN_PASSWORD,
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