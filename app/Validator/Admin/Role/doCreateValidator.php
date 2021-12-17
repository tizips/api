<?php

declare(strict_types=1);

namespace App\Validator\Admin\Role;

use App\Validator\Validator;

class doCreateValidator extends Validator
{
    protected function rule(): array
    {
        return [
            'name' => [
                'required', 'max:20',
            ],
            'permissions' => [
                'required', 'array',
            ],
            'permissions.*' => [
                'required', 'integer', 'gt:0',
            ],
            'summary' => [
                'nullable', 'max:255',
            ],
        ];
    }
}