<?php

/*
 * Today is the tomorrow you promised to act yesterday.
 */

namespace Mitoop\Snowflake;

class RandomSequenceStrategy implements SequenceStrategyInterface
{
    public function generate(int $currentTime): int
    {
        return random_int(0, Snowflake::getMaxSequence());
    }
}
