# 安装

这是一个基于Hyperf框架构建的服务应用，开发过程中遇到问题可以查阅 [Hyperf](https://hyperf.wiki/2.0/#/zh-cn/quick-start/install) 官方文档

# 环境依赖
  
Hyperf对系统环境有一些要求，只能在Linux和Mac环境下运行，但由于Docker虚拟化技术的发展，Windows下的Docker也可以作为Windows下的运行环境。

各种版本的Dockerfile已经在 [hyperf\hyperf-docker](https://github.com/hyperf/hyperf-docker) 项目中为您准备好了，或者直接基于已经构建好的 [hyperf\hyperf](https://hub.docker.com/r/hyperf/hyperf) 映像运行。

当你不想使用Docker作为你的运行环境的基础时，你需要确保你的操作环境符合以下要求:

 - PHP >= 7.2
 - Swoole PHP extension >= 4.4，and Disabled `Short Name`
 - OpenSSL PHP extension
 - JSON PHP extension
 - PDO PHP extension （If you need to use MySQL Client）
 - Redis PHP extension （If you need to use Redis Client）
 - Protobuf PHP extension （If you need to use gRPC Server of Client）

# 初始化与启动

### 初始化
将代码仓库 ``clone`` 至本地，使用 ``composer install -vvv`` 进行依赖安装


### 启动

```php
$ cd path/to/install
$ php bin/hyperf.php start
```

项目将会以CLI模式启动，并默认监听 ``9501`` 端口（可通过 ``.env`` 文件中的 SERVER_HTTP_PORT 修改），然后可以通过 http://localhost:9501/ 访问该站点。
