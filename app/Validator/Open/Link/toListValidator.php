<?php

declare(strict_types=1);

namespace App\Validator\Open\Link;

use App\Validator\Validator;

class toListValidator extends Validator
{
    protected function rule(): array
    {
        return [
            'position' => [
                'nullable', 'integer', 'between:0,2',
            ],
        ];
    }
}