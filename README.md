# Snowflake 雪花算法

## 说明
雪花算法(64bits)的 PHP 实现

## 要求
1. PHP >= 7.0

## 安装
```shell
$ composer require mitoop/snowflake
```

## 使用
```php
use Mitoop\Snowflake\Snowflake;

// 初始化
$snowflake = new Snowflake('2020-10-24 10:24:00');
// 设置数据中心ID及机器ID(范围: 0-31)，默认用随机数
$snowflake->setDatacenterId(1);
$snowflake->setWorkerId(1);
// 设置生成序列号策略 不设置默认就是用的随机数策略
$snowflake->setSequenceStrategy(new RandomSequenceStrategy());
// here we go
$snowflake->id();
```

## Tips
雪花 id 长度`[7-19]`位，实践中，选择合适的纪元时间，使生成的雪花 id 从指定长度开始

使用 `RedisSequenceStrategy` 时，如果 Redis 的 `eval()` 返回 `false`，生成流程会抛出 `UnexpectedValueException`，不会将失败结果作为序列号 `0` 继续生成 ID。Redis 自身抛出的异常会继续向上传播。

## 测试

不需要安装 Composer 依赖即可运行回归测试（`-n` 用于隔离 Redis 扩展，加载测试替身）：

```shell
php -n tests/redis-sequence.php
```

真实 Redis 测试需要 phpredis 扩展和一个可丢弃的独立 Redis 实例。测试会临时将该实例的 `maxmemory` 设为 `1` 来模拟 OOM，结束时恢复原值。请勿连接共享或生产实例。设置 `SNOWFLAKE_TEST_REDIS_ISOLATED=1` 和 `SNOWFLAKE_TEST_REDIS_PORT` 后运行：

```shell
php tests/redis-integration.php
```
