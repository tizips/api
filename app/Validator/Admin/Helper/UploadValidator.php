<?php

declare(strict_types=1);

namespace App\Validator\Admin\Helper;

use App\Helper\Pattern;
use App\Validator\Validator;

class UploadValidator extends Validator
{
    protected function rule(): array
    {
        return [
            'dir' => [
                'required', sprintf('regex:%s', Pattern::FILE_DIR),
            ],
            'file' => [
                'required', 'file',
            ],
        ];
    }

    protected function message(): array
    {
        return [
            'dir.regex' => '文件目录格式错误',
        ];
    }
}