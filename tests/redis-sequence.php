<?php

use Mitoop\Snowflake\RedisSequenceStrategy;
use Mitoop\Snowflake\Snowflake;

if (class_exists('Redis', false)) {
    fwrite(STDERR, "Run this test without the Redis extension: php -n tests/redis-sequence.php\n");
    exit(1);
}

require __DIR__.'/RedisStub.php';
require __DIR__.'/../src/SequenceStrategyInterface.php';
require __DIR__.'/../src/RedisSequenceStrategy.php';
require __DIR__.'/../src/Snowflake.php';

$tests = [
    'false stops ID generation' => function () {
        $redis = new Redis;
        $redis->result = false;
        $snowflake = (new Snowflake('2020-01-01'))
            ->setDatacenterId(1)
            ->setWorkerId(1)
            ->setSequenceStrategy(new RedisSequenceStrategy($redis));

        try {
            $snowflake->id();
        } catch (UnexpectedValueException $exception) {
            return;
        }

        throw new RuntimeException('Expected UnexpectedValueException instead of an ID.');
    },
    'zero remains a valid sequence' => function () {
        $redis = new Redis;
        $redis->result = 0;

        $sequence = (new RedisSequenceStrategy($redis))->generate(123456);

        if ($sequence !== 0) {
            throw new RuntimeException('Expected integer zero.');
        }
    },
    'positive sequence is returned unchanged' => function () {
        $redis = new Redis;
        $redis->result = 42;

        $sequence = (new RedisSequenceStrategy($redis))->generate(123456);

        if ($sequence !== 42) {
            throw new RuntimeException('Expected integer 42.');
        }
    },
    'Redis exceptions propagate unchanged' => function () {
        $redis = new Redis;
        $redis->exception = new RuntimeException('Redis connection failed.');

        try {
            (new RedisSequenceStrategy($redis))->generate(123456);
        } catch (RuntimeException $exception) {
            if ($exception === $redis->exception) {
                return;
            }

            throw $exception;
        }

        throw new RuntimeException('Expected the original Redis exception.');
    },
];

$failures = 0;
foreach ($tests as $name => $test) {
    try {
        $test();
        echo 'PASS: '.$name.PHP_EOL;
    } catch (Throwable $exception) {
        $failures++;
        fwrite(STDERR, 'FAIL: '.$name.': '.$exception->getMessage().PHP_EOL);
    }
}

exit($failures === 0 ? 0 : 1);
