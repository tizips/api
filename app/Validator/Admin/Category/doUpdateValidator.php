<?php

declare(strict_types=1);

namespace App\Validator\Admin\Category;

use App\Constants\EnableConstants;
use App\Helper\Pattern;
use App\Model\Category;
use App\Validator\Validator;
use Hyperf\Validation\Rule;

class doUpdateValidator extends Validator
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
            'uri' => [
                Rule::requiredIf(function () {
                    return (int) $this->request->input('parent') > 0;
                }), 'max:60', 'regex:' . Pattern::URI,
            ],
            'picture' => [
                Rule::requiredIf(function () {
                    return ! empty($this->request->input('parent'));
                }), 'max:120', 'active_url',
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
            'no' => [
                'required', 'integer', 'between:1,100',
            ],
            'is_page' => [
                'required', Rule::in([Category::IS_PAGE_YES, Category::IS_PAGE_NO]),
            ],
            'is_comment' => [
                'required', Rule::in([Category::IS_COMMENT_YES, Category::IS_COMMENT_NO]),
            ],
            'is_enable' => [
                'required', Rule::in([EnableConstants::IS_ENABLE_YES, EnableConstants::IS_ENABLE_NO]),
            ],
            'page' => [
                Rule::requiredIf(function () {
                    return $this->request->input('is_page') == Category::IS_PAGE_YES;
                }),
            ],
        ];
    }
}