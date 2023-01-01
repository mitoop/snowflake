<?php

namespace Mitoop\Snowflake;

use Redis;

class RedisSequenceStrategy implements SequenceStrategyInterface
{
    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * @var string
     */
    protected $cachePrefix;

    public function __construct(Redis $redis, $cachePrefix = 'snowflake:')
    {
        $this->redis = $redis;
        $this->cachePrefix = $cachePrefix;
    }

    public function generate(int $currentTime)
    {
        $lua = <<<'LUA'
if redis.call('set', KEYS[1], ARGV[1], "EX", ARGV[2], "NX") then
    return 0
else
    return redis.call('incr', KEYS[1])
end
LUA;

        return $this->redis->eval($lua, [$this->cachePrefix.$currentTime, 0, 5], 1);
    }
}
