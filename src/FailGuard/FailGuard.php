<?php

namespace RobinTheHood\ExceptionMonitor\FailGuard;

class FailGuard
{
    private $client;

    public function __construct()
    {
        $this->loadClient();
    }

    public function loadClient()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $clientId = new clientId($ip);

        $root = $_SERVER["DOCUMENT_ROOT"];
        $path = $root . '/FailGuard/Clients/' . $clientId->getId();

        $this->client = new Client($clientId);
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $this->client = unserialize($content);
        }

        if ($this->client->isUnbanned()) {
            $this->client = new Client($clientId);
        }
    }

    public function saveClient()
    {
        $clientId = $this->client->getClientId();
        $root = $_SERVER["DOCUMENT_ROOT"];
        $path = $root . '/FailGuard/Clients/' . $clientId->getId();

        $content = serialize($this->client);
        file_put_contents($path, $content);
    }

    public function addFail(): void
    {
        $this->client->addFail();

        if ($this->checkForBan($this->client)) {
            $this->client->setBan(300); // seconds
        }
    }

    public function isBanned(): bool
    {
        return $this->client->isBanned();
    }

    private function checkForBan($client): bool
    {
        if ($client->getAvagrageTime() == -1) {
            return false;
        }

        if ($client->getAvagrageTime() > 5) {
            return false;
        }

        if ($client->getFailCount() < 5) {
            return false;
        }

        return true;
    }
}
