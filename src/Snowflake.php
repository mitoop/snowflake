<?php

namespace Mitoop\Snowflake;

use InvalidArgumentException;

final class Snowflake
{
    const SEQUENCE_BITS = 12;

    const WORKER_ID_BITS = 5;

    const DATACENTER_ID_BITS = 5;

    // const TIMESTAMP_BITS = 41;

    const MAX_SEQUENCE = -1 ^ (-1 << self::SEQUENCE_BITS);

    const MAX_WORK_ID = -1 ^ (-1 << self::WORKER_ID_BITS);

    const MAX_DATACENTER_ID = -1 ^ (-1 << self::DATACENTER_ID_BITS);

    // const MAX_TIMESTAMP = -1 ^ (-1 << self::TIMESTAMP_BITS);

    const WORK_ID_SHIFT = self::SEQUENCE_BITS;

    const DATACENTER_ID_SHIFT = self::SEQUENCE_BITS + self::WORKER_ID_BITS;

    const TIMESTAMP_SHIFT = self::SEQUENCE_BITS + self::WORKER_ID_BITS + self::DATACENTER_ID_BITS;

    private $datacenterId = -1;

    private $workerId = -1;

    private $epoch = 1643738522000;

    private $sequenceStrategy;

    public function __construct(SequenceStrategyInterface $strategy = null)
    {
        $this->sequenceStrategy = $strategy ?? new RandomSequenceStrategy();
    }

    public function id(): int
    {
        return $this->getId();
    }

    public function getId(): int
    {
        $currentMillisecond = $this->getCurrentMillisecond();

        while (($sequence = $this->getSequence($currentMillisecond)) > self::MAX_SEQUENCE) {
            usleep(1000);
            $currentMillisecond = $this->getCurrentMillisecond();
        }

        return (($currentMillisecond - $this->getEpoch()) << self::TIMESTAMP_SHIFT) |
               ($this->getDatacenterId() << self::DATACENTER_ID_SHIFT) |
               ($this->getWorkerId() << self::WORK_ID_SHIFT) |
               $sequence;
    }

    private function getCurrentMillisecond(): int
    {
        return (int) (microtime(true) * 1000);
    }

    private function getSequence(int $currentTime): int
    {
        return (int) $this->sequenceStrategy->generate($currentTime);
    }

    public function setEpoch(string $epoch): Snowflake
    {
        $epoch = strtotime($epoch);

        if (false === $epoch || $epoch < 0) {
            throw new InvalidArgumentException('incorrect epoch string, correct epoch string like : 2020-10-24 10:24:00');
        }

        if (time() - $epoch < 0) {
            throw new InvalidArgumentException('the epoch cannot be greater than the current time');
        }

        $this->epoch = $epoch * 1000;

        return $this;
    }

    private function getEpoch(): int
    {
        return $this->epoch;
    }

    public function setDatacenterId(int $datacenterId): Snowflake
    {
        if ($datacenterId > self::MAX_DATACENTER_ID || $datacenterId < 0) {
            throw new InvalidArgumentException(sprintf("worker Id can't be greater than %d or less than 0", self::MAX_DATACENTER_ID));
        }

        $this->datacenterId = $datacenterId;

        return $this;
    }

    private function getDatacenterId(): int
    {
        return -1 === $this->datacenterId ? random_int(0, self::MAX_DATACENTER_ID) : $this->datacenterId;
    }

    public function setWorkerId(int $workerId): Snowflake
    {
        if ($workerId > self::MAX_WORK_ID || $workerId < 0) {
            throw new InvalidArgumentException(sprintf("datacenter Id can't be greater than %d or less than 0", self::MAX_WORK_ID));
        }

        $this->workerId = $workerId;

        return $this;
    }

    private function getWorkerId(): int
    {
        return -1 === $this->workerId ? random_int(0, self::MAX_WORK_ID) : $this->workerId;
    }

    public function setSequenceStrategy(SequenceStrategyInterface $strategy): Snowflake
    {
        $this->sequenceStrategy = $strategy;

        return $this;
    }

    public static function getMaxSequence(): int
    {
        return self::MAX_SEQUENCE;
    }
}
