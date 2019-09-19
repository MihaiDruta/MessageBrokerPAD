<?php

class Server
{
    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var int */
    private $maxClients;

    public function __construct(string $host, int $port, int $maxClients)
    {
        $this->host = $host;
        $this->port = $port;
        $this->maxClients = $maxClients;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getMaxClients(): int
    {
        return $this->maxClients;
    }
}
