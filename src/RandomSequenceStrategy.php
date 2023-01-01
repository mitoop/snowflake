<?php

namespace Mitoop;

class RandomSequenceStrategy implements SequenceStrategyInterface
{
    /**
     * @throws \Exception
     */
    public function generate(int $currentTime): int
    {
        return random_int(0, Snowflake::getMaxSequence());
    }
}
