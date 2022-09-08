# Snowflake 雪花算法

## 说明
雪花算法(64bits)的 PHP 实现

## 要求
1. PHP >= 7.4

## 安装
```shell
$ composer require mitoop/snowflake
```

## 使用
```php
$snowflake = new \Mitoop\Snowflake\Snowflake;

// 指定序列号策略，默认用随机策略
// $snowflake = new \Mitoop\Snowflake\Snowflake(new RandomSequenceStrategy);

// 设置纪元时间
$snowflake->setEpoch('2020-10-24 10:24:00');

// 设置数据中心ID及机器ID(范围: 0-31)，默认用随机数
$snowflake->setDatacenterId(1);
$snowflake->setWorkerId(1);

// here we go
$snowflake->id();
```

## Tips
雪花 id 长度`[7-19]`位，实践中，选择合适的纪元时间，使生成的雪花 id 从指定长度开始
