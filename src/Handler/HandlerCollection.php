<?php

declare(strict_types=1);

namespace RobinTheHood\ExceptionMonitor\Handler;

use Iterator;

class HandlerCollection implements Iterator
{
    /** @var HandlerInterface[] */
    private $handlers = [];

    /** @var int */
    private $position = 0;

    public function add(HandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    public function current(): HandlerInterface
    {
        return $this->handlers[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->handlers[$this->position]);
    }
}
