<?php

declare(strict_types=1);

namespace RobinTheHood\ExceptionMonitor\Handler;

use Throwable;

interface HandlerInterface
{
    public function handle(Throwable $exception): void;
}
