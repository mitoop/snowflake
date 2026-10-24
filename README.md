# Snowflake

64 位雪花算法的 PHP 实现。

## 要求

- PHP >= 7.0
- 使用 `RedisSequenceStrategy` 时需要 [phpredis](https://github.com/phpredis/phpredis) 扩展

## 安装

```shell
composer require mitoop/snowflake
```

## 快速开始

```php
use Mitoop\Snowflake\Snowflake;

$snowflake = (new Snowflake('2020-10-24 10:24:00'))
    ->setDatacenterId(1) // 0–31，不设置则每次随机
    ->setWorkerId(1);    // 0–31，不设置则每次随机

$id = $snowflake->id();
```

默认序列策略是 `RandomSequenceStrategy`（进程内随机序列号）。多机 / 多进程要保证同一毫秒内不撞号时，请改用 Redis 策略。

## Redis 序列策略

```php
use Mitoop\Snowflake\RedisSequenceStrategy;
use Mitoop\Snowflake\Snowflake;
use Redis;

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$snowflake = (new Snowflake('2020-10-24 10:24:00'))
    ->setDatacenterId(1)
    ->setWorkerId(1)
    ->setSequenceStrategy(new RedisSequenceStrategy($redis)); // 可选第二参数：key 前缀，默认 snowflake:

$id = $snowflake->id();
```

`RedisSequenceStrategy` 用 Lua 在 Redis 里按毫秒自增序列；key 带 5 秒过期。

若 `eval()` 返回 `false`（连接失败、OOM 等），会抛出 `RuntimeException`，并附带 `getLastError()` 信息，**不会**把失败当成序列号 `0` 继续发号。Redis 客户端自身抛出的异常会原样向上抛。

## 解析 ID

```php
$parts = $snowflake->parseId($id, true);
// timestamp / datacenter_id / worker_id / sequence（十进制）
```

第二个参数为 `false`（默认）时，各字段仍是二进制字符串。

## 说明

| 字段 | 位数 | 范围 |
|------|------|------|
| 时间戳（相对纪元） | 41 | — |
| 数据中心 | 5 | 0–31 |
| 机器 | 5 | 0–31 |
| 序列号 | 12 | 0–4095 |

- 纪元时间（构造函数参数）不能晚于当前时间。
- 生成 ID 长度大约在 7–19 位；把纪元设得更近，ID 会更短。
- 同一毫秒内序列超过 4095 时会短暂等待下一毫秒再生成。

## 测试

无需 Composer 依赖即可跑回归测试（`-n` 禁用扩展，改用 Redis 替身）：

```shell
composer test
# 或
php -n tests/redis-sequence.php
```

真实 Redis 集成测试需要 phpredis，以及**可丢弃的独立实例**（会临时把 `maxmemory` 设为 `1` 模拟 OOM，结束后恢复）。不要连共享 / 生产库：

```shell
SNOWFLAKE_TEST_REDIS_ISOLATED=1 SNOWFLAKE_TEST_REDIS_PORT=6379 composer test:redis
```

## License

MIT
