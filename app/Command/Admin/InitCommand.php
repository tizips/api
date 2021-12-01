<?php

declare(strict_types=1);

namespace App\Command\Admin;

use App\Helper\Admin;
use App\Model\System;
use Carbon\Carbon;
use Exception;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\DbConnection\Db;
use Psr\Container\ContainerInterface;
use Throwable;

#[Command]
class InitCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('admin:init');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('初始化系统');
    }

    public function handle()
    {
        $this->warn('执行此操作前，请先确定数据库配置正确并执行过数据迁移！');

        $confirm = $this->confirm('确定并继续配置');

        if (! $confirm) {
            $this->warn('操作已取消！');
            return;
        }

        Db::beginTransaction();

        try {

            foreach (Admin::KEYS as $key => $item) {
                $systems = System::query()->where('type', $key)->get();
                $keys = array_column($systems->toArray(), 'key');

                $create = array_diff(array_column($item, 'key'), $keys);
                $delete = array_diff($keys, array_column($item, 'key'));

                if ($create) {
                    $now = Carbon::now();

                    $creates = [];

                    foreach ($create as $val) {
                        foreach ($item as $v) {
                            if ($val == $v['key']) {
                                $creates[] = [
                                    'type' => $key,
                                    'genre' => $v['genre'],
                                    'label' => $v['label'],
                                    'key' => $val,
                                    'val' => null,
                                    'required' => $v['required'],
                                    'created_at' => $now->toDateTimeString(),
                                    'updated_at' => $now->toDateTimeString(),
                                ];
                            }
                        }
                    }

                    $ok = System::query()->insert($creates);
                    if (! $ok) new Exception(sprintf('增加失败：%s。keys：%s', $key, implode(' / ', $create)));
                }

                if ($delete) {
                    $ok = System::query()
                        ->where('type', $key)
                        ->whereIn('key', $delete)
                        ->delete();
                    if (! $ok) new Exception(sprintf('删除失败：%s。keys：%s', $key, implode(' / ', $delete)));
                }
            }

            Db::commit();
        } catch (Throwable $exception) {
            Db::rollBack();
            $this->error($exception->getMessage());
            return;
        }

        $this->info('初始化成功！');
    }
}
