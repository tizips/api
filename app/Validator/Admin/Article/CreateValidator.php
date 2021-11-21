<?php

declare(strict_types=1);

namespace App\Validator\Admin\Article;

use App\Constants\EnableConstants;
use App\Model\Category;
use App\Validator\Validator;
use Hyperf\Validation\Rule;

class CreateValidator extends Validator
{
    protected function rule(): array
    {
        return [
            'category' => [
                'required', 'integer', 'gt:0',
            ],
            'name' => [
                'required', 'max:120',
            ],
            'picture' => [
                'nullable', 'max:120', 'active_url',
            ],
            'title' => [
                'nullable', 'max:120',
            ],
            'keyword' => [
                'nullable', 'max:120',
            ],
            'description' => [
                'nullable', 'max:120',
            ],
            'source_name' => [
                'nullable', 'max:20',
            ],
            'source_uri' => [
                Rule::requiredIf(function () {
                    return ! empty($this->request->input('source_name'));
                }), 'max:120', 'active_url'
            ],
            'is_comment' => [
                'required', Rule::in([Category::IS_COMMENT_YES, Category::IS_COMMENT_NO]),
            ],
            'is_enable' => [
                'required', Rule::in([EnableConstants::IS_ENABLE_YES, EnableConstants::IS_ENABLE_NO]),
            ],
            'content' => [
                'required',
            ],
        ];
    }
}