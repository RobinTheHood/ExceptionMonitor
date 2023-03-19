<?php

declare(strict_types=1);

namespace RobinTheHood\ExceptionMonitor\Handler;

use InvalidArgumentException;
use Throwable;

class CallbackHandler implements HandlerInterface
{
    /** @var callable */
    private $callable;

    public function __construct(callable $callable)
    {
        if (!is_callable($callable)) {
            throw new InvalidArgumentException('CallbackHandler::__construct needs a valid callable');
        }

        $this->callable = $callable;
    }

    public function handle(Throwable $exception): void
    {
        $callable = $this->callable;
        $callable($exception);
    }
}
