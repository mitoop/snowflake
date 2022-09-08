<h1 align="center">Snowflake</h1>

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

// 指定序列号策略 默认用随机策略
// $snowflake = new \Mitoop\Snowflake\Snowflake(new RandomSequenceStrategy);

// 设置开始时间
$snowflake->setEpoch('2021-09-08 10:10:10)')

// 设置数据中心ID及机器ID(范围: 0-31) 不设置就用随机数
$snowflake->setDatacenterId(1);
$snowflake->setWorkerId(1);

// here we go
$snowflake->id();
```
