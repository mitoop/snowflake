<?php

namespace Mitoop\Snowflake;

interface SequenceStrategyInterface
{
    public function generate(int $currentTime);
}
