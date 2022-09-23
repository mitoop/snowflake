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
