<?php

namespace Mitoop;

interface SequenceStrategyInterface
{
    public function generate(int $currentTime);
}
