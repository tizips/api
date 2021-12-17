<?php

declare(strict_types=1);

namespace App\Validator\Open\Article;

use App\Validator\Validator;

class toSearchValidator extends Validator
{
    protected function rule(): array
    {
        return [
            'keyword' => [
                'required', 'max:60',
            ],
            'page' => [
                'nullable', 'integer', 'gte:1',
            ],
            'size' => [
                'nullable', 'integer', 'between:2,20',
            ],
        ];
    }
}