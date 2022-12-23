<?php

declare(strict_types=1);

namespace RobinTheHood\ExceptionMonitor\FailGuard;

class ClientId
{
    private $ip;

    public function __construct(string $ip)
    {
        $this->ip = $ip;
    }

    public function getId()
    {
        return md5($this->ip);
    }
}
