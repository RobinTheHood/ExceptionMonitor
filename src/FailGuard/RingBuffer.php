<?php

declare(strict_types=1);

namespace RobinTheHood\ExceptionMonitor\FailGuard;

class RingBuffer
{
    private $size;
    private $pointer = 0;
    private $values = [];

    public function __construct(int $size)
    {
        $this->size = $size;
    }

    public function addValue($value): void
    {
        $this->values[$this->pointer] = $value;
        $this->pointer = ($this->pointer + 1) % $this->size;
    }

    public function getValues(): array
    {
        return $this->values;
    }
}
