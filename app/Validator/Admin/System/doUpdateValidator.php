<?php

declare(strict_types=1);

namespace App\Validator\Admin\System;

use App\Constants\EnableConstants;
use App\Enum\System\TypeEnum;
use App\Helper\Admin;
use App\Model\System;
use App\Validator\Validator;
use Hyperf\Validation\Rule;
use HyperfExt\Enum\Rules\EnumValue;

class doUpdateValidator extends Validator
{
    protected function rule(): array
    {
        $rules = [
            'type' => [
                'required', new EnumValue(TypeEnum::class),
            ],
            'data' => [
                'required', 'array',
            ],
        ];

        $type = $this->request->input('type');

        if ($type && isset(Admin::KEYS[$type])) {
            foreach (Admin::KEYS[$type] as $item) {
                $rule = [];
                if ($item['required'] == System::REQUIRED_YES) $rule[] = 'required';
                if ($item['genre'] == System::GENRE_INPUT) $rule[] = 'max:20';
                if ($item['genre'] == System::GENRE_ENABLE) $rule[] = Rule::in([EnableConstants::IS_ENABLE_YES, EnableConstants::IS_ENABLE_NO]);
                if ($item['genre'] == System::GENRE_URL) $rule[] = 'active_url';
                if ($item['genre'] == System::GENRE_EMAIL) $rule[] = 'email';
                $key = 'data.' . $item['key'];
                $rules[$key] = $rule;
            }
        }

        return $rules;
    }
}