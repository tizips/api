# 安装

这是一个基于 Hyperf 框架构建的服务应用，开发过程中遇到问题可以查阅 [Hyperf](https://hyperf.wiki/2.0/#/zh-cn/quick-start/install) 官方文档

# 环境依赖

Hyperf 对系统环境有一些要求，只能在 Linux 和 Mac 环境下运行，但由于 Docker 虚拟化技术的发展，Windows 下的 Docker 也可以作为 Windows 下的运行环境。

各种版本的 Dockerfile 已经在 [hyperf\hyperf-docker](https://github.com/hyperf/hyperf-docker) 项目中为您准备好了，或者直接基于已经构建好的 [hyperf\hyperf](https://hub.docker.com/r/hyperf/hyperf) 映像运行。

当你不想使用 Docker 作为你的运行环境的基础时，你需要确保你的操作环境符合以下要求:

- PHP >= 8.0
- Swoole PHP extension >= 4.7，and Disabled `Short Name`
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

### 热更新
```php
$ cd path/to/install
$ php bin/hyperf.php server:watch
```

项目将会以CLI模式启动，并默认监听 ``9501`` 端口（可通过 ``.env`` 文件中的 SERVER_HTTP_PORT 修改），然后可以通过 http://localhost:9501/ 访问该站点。

# Docker 运行（挂载主机目录）

创建网络
```bash
$ docker network create --subnet=172.19.0.0/16 service
```

### 编译镜像
```bash
$ docker build -t hyperf/blog .
```

### 运行容器
```bash
$ docker run \
    --name hyperf-blog \
    --net service \
    --ip 172.19.0.100 \
    --privileged=true \
    -v $PWD/:/opt/www \
    -itd \
    hyperf/blog
```

### 复制容器目录到宿主机
```bash
$ docker cp hyperf-blog:/opt/www/* ./
```

### 设置容器自动启动
```bash
$ docker update --restart=always hyperf-blog
```

首次映射目录运行容器，会因为项目没有执行 Composer 命令而运行失败。
建议先执行之后，将文件全部拷贝出来，再映射目录的方式运行

Casbin 将缓存于内存中运行，禁止从数据库中直接操作权限相关数据。若有必要，请清空 Casbin 数据之后，重新生成。

清空已生成的 Casbin 数据
```php
php bin/hyperf.php casbin:clear
```

生成 Casbin 数据
```php
php bin/hyperf.php casbin:make
```