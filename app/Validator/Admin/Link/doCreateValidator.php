<?php

declare(strict_types=1);

namespace App\Validator\Admin\Link;

use App\Constants\EnableConstants;
use App\Model\Link;
use App\Validator\Validator;
use Hyperf\Validation\Rule;

class doCreateValidator extends Validator
{
    protected function rule(): array
    {
        return [
            'name' => [
                'required', 'max:20',
            ],
            'uri' => [
                'required', 'max:20', 'active_url',
            ],
            'no' => [
                'required', 'integer', 'between:1,100',
            ],
            'is_enable' => [
                'required', Rule::in([EnableConstants::IS_ENABLE_YES, EnableConstants::IS_ENABLE_NO]),
            ],
            'summary' => [
                'nullable', 'max:120',
            ],
            'logo' => [
                'nullable', 'max:120', 'active_url',
            ],
            'position' => [
                'required', Rule::in([Link::POSITION_ALL, Link::POSITION_BOTTOM, Link::POSITION_OTHER])
            ],
            'admin' => [
                'nullable', 'max:20',
            ],
            'email' => [
                Rule::requiredIf(function () {
                    return ! empty($this->request->input('admin'));
                }), 'max:120',
            ],
        ];
    }
}