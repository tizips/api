<?php

declare(strict_types=1);

namespace App\Request\Auth;

use App\Common\Api\Status;
use Hyperf\Validation\Request\FormRequest;

class JwtTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'username' => 'bail|required|between:5,20|regex:/^[a-zA-Z0-9\-\_]{5,20}$/i',
            'password' => 'bail|required|between:6,20|regex:/^[a-zA-Z0-9\-\_\@\$\&\%\!]{5,20}$/i',
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => '用户名或密码错误',
            'password.regex' => '用户名或密码错误',
        ];
    }
}
