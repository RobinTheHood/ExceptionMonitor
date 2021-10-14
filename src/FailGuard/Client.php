<?php

declare(strict_types=1);

namespace RobinTheHood\ExceptionMonitor\FailGuard;

use RobinTheHood\ExceptionMonitor\FailGuard\ClientId;
use RobinTheHood\ExceptionMonitor\FailGuard\RingBuffer;

class Client
{
    private $clientId;
    private $timestamps;
    private $failCount = 0;
    private $enterJailTime = 0;
    private $secondsInJail = 300;

    public function __construct(ClientId $clientId)
    {
        $this->clientId = $clientId;
        $this->timestamps = new RingBuffer(5);
    }

    public function getClientId(): ClientId
    {
        return $this->clientId;
    }

    public function setFailCount(int $failCount): void
    {
        $this->failCount = $failCount;
    }

    public function getFailCount(): int
    {
        return $this->failCount;
    }

    public function setBan(int $secondsInJail)
    {
        $this->enterJailTime = time();
        $this->secondsInJail = $secondsInJail;
    }

    public function isBanned()
    {
        if ($this->enterJailTime <= 0) {
            return false;
        }

        $timeDiff = time() - $this->enterJailTime;
        if ($timeDiff > $this->secondsInJail) {
            return false;
        }

        return true;
    }

    public function isUnbanned()
    {
        if ($this->enterJailTime <= 0) {
            return false;
        }

        $timeDiff = time() - $this->enterJailTime;
        if ($timeDiff < $this->secondsInJail) {
            return false;
        }

        return true;
    }

    public function addFail()
    {
        $this->failCount++;
        $this->addTimestamp(time());
    }

    public function addTimestamp(int $timestamp)
    {
        $this->timestamps->addValue($timestamp);
        return $timestamp;
    }

    public function getAvagrageTime(): float
    {
        $values = $this->timestamps->getValues();

        if (count($values) < 2) {
            return -1.0;
        }

        $deltaValues = [];
        for ($i = 1; $i < count($values); $i++) {
            $deltaValues[] = $values[$i] - $values[$i - 1];
        }

        $sum = 0.0;
        foreach ($deltaValues as $deltaValue) {
            $sum += $deltaValue;
        }

        return $sum / count($deltaValues);
    }
}
