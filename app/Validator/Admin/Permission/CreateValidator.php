<?php

declare(strict_types=1);

namespace App\Validator\Admin\Permission;

use App\Helper\Method;
use App\Helper\Pattern;
use App\Validator\Validator;
use Hyperf\Validation\Rule;

class CreateValidator extends Validator
{
    protected function rule(): array
    {
        return [
            'parent' => [
                'nullable', 'integer', 'gte:0',
            ],
            'name' => [
                'required', 'max:20',
            ],
            'slug' => [
                'required', 'max:60', 'regex:' . Pattern::PERMISSION_SLUG,
            ],
            'method' => [
                'nullable', Rule::requiredIf(function () {
                    return ! empty($this->request->input('path'));
                }),
                Rule::in([Method::POST, Method::PUT, Method::GET, Method::DELETE]),
            ],
            'path' => [
                'nullable', Rule::requiredIf(function () {
                    return ! empty($this->request->input('method'));
                }), 'regex:' . Pattern::PERMISSION_PATH,
            ],
        ];
    }
}