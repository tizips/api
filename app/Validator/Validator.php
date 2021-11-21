<?php

declare(strict_types=1);

namespace App\Validator;

use App\Exception\ApiException;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Request;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Validator
{
    #[Inject]
    private ValidatorFactoryInterface $validator;

    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected Request $request;

    /**
     * 开始验证
     * @param bool $file 是否包含文件验证
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function make(bool $file = false)
    {
        self::init()->request()->valid($file);
    }

    /**
     * 验证规则
     * @return array
     */
    protected function rule(): array
    {
        return [];
    }

    /**
     * 自定义验证错误信息
     * @return array
     */
    protected function message(): array
    {
        return [];
    }

    /**
     * 初始化验证类
     * @return Validator
     */
    private static function init(): Validator
    {
        $Class = get_called_class();

        if (! $Class) {
            ApiException::break('参数校验失败！', 40000);
        }

        return new $Class();
    }

    /**
     * 获取本次请求内容
     * @return Validator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function request(): Validator
    {
        $this->request = $this->container->get(Request::class);

        return $this;
    }

    /**
     * 验证操作
     * @param bool $file
     */
    private function valid(bool $file)
    {
        if ($this->isEmptyRule()) return;

        $data = $this->request->all();

        if ($file) {
            $data = array_replace_recursive($this->request->all(), $this->request->getUploadedFiles());
        }

        $rule = $this->rule();

        //  第一次验证失败停止验证
        foreach ($rule as &$item) {
            if (is_array($item) && ! in_array('bail', $item)) array_unshift($item, 'bail');
        }

        $valid = $this->validator->make($data, $rule, $this->message());

        if ($valid->fails()) {
            $this->failed($valid);
        } else {
            $this->success();
        }
    }

    /**
     * 验证成功执行操作
     */
    public function success()
    {

    }

    /**
     * 验证失败执行操作
     * @param ValidatorInterface $validator
     */
    public function failed(ValidatorInterface $validator)
    {
        ApiException::break($validator->errors()->first(), 40000);
    }

    /**
     * 判断验证规则是否为空
     * @return bool
     */
    private function isEmptyRule(): bool
    {
        return empty($this->rule());
    }
}