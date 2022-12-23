<?php

namespace RobinTheHood\ExceptionMonitor;

class Server
{
    /**
     * @return string
     */
    public function getIpAddress()
    {
        return isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
    }

    /**
     * @return string
     */
    public function getServerName()
    {
        return isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
    }
}
