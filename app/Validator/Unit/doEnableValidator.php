<?php

declare(strict_types=1);

namespace App\Validator\Unit;

use App\Constants\EnableConstants;
use App\Validator\Validator;
use Hyperf\Validation\Rule;

class doEnableValidator extends Validator
{
    protected function rule(): array
    {
        return [
            'id' => [
                'required', 'integer', 'gt:0',
            ],
            'enable' => [
                'required', Rule::in([EnableConstants::IS_ENABLE_YES, EnableConstants::IS_ENABLE_NO]),
            ],
        ];
    }
}