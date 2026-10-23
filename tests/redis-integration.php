<?php

use Mitoop\Snowflake\RedisSequenceStrategy;
use Mitoop\Snowflake\Snowflake;

require __DIR__.'/../src/SequenceStrategyInterface.php';
require __DIR__.'/../src/RedisSequenceStrategy.php';
require __DIR__.'/../src/Snowflake.php';

if (! extension_loaded('redis') || getenv('SNOWFLAKE_TEST_REDIS_ISOLATED') !== '1' || ! getenv('SNOWFLAKE_TEST_REDIS_PORT')) {
    fwrite(STDERR, "Requires phpredis and an isolated disposable Redis instance. Set SNOWFLAKE_TEST_REDIS_ISOLATED=1 and SNOWFLAKE_TEST_REDIS_PORT.\n");
    exit(1);
}

$redis = new Redis;
$redis->connect('127.0.0.1', (int) getenv('SNOWFLAKE_TEST_REDIS_PORT'));
$prefix = 'snowflake-test:'.uniqid('', true).':';
$originalLimit = $redis->config('GET', 'maxmemory');
$strategy = new RedisSequenceStrategy($redis, $prefix);
$failed = false;

try {
    if ($strategy->generate(123456) !== 0 || $strategy->generate(123456) !== 1) {
        throw new RuntimeException('Expected the first two Redis sequences to be 0 and 1.');
    }
    echo "PASS: real Redis sequences 0 and 1\n";

    $snowflake = (new Snowflake('2020-01-01'))
        ->setDatacenterId(1)
        ->setWorkerId(1)
        ->setSequenceStrategy($strategy);
    if (! $redis->config('SET', 'maxmemory', '1')) {
        throw new RuntimeException('Could not configure the isolated Redis failure.');
    }
    $redis->clearLastError();

    $stopped = false;
    try {
        $snowflake->id();
    } catch (UnexpectedValueException $exception) {
        $stopped = true;
    }

    if (! $stopped || strpos((string) $redis->getLastError(), 'OOM') === false) {
        throw new RuntimeException('Expected Redis OOM to stop ID generation with UnexpectedValueException.');
    }
    echo "PASS: real Redis OOM stops ID generation\n";
} catch (Throwable $exception) {
    $failed = true;
    fwrite(STDERR, 'FAIL: '.$exception->getMessage().PHP_EOL);
} finally {
    $redis->config('SET', 'maxmemory', $originalLimit['maxmemory']);
    $redis->del($prefix.'123456');
    $redis->close();
}

exit($failed ? 1 : 0);
