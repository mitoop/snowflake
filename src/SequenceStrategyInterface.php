<?php

/*
 * Today is the tomorrow you promised to act yesterday.
 */

namespace Mitoop\Snowflake;

interface SequenceStrategyInterface
{
    public function generate(int $currentTime);
}
