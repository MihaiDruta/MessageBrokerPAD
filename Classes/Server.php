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

    /**
     * @throws SocketCreationError
     */
    public function createSocket()
    {
        if (!($socket = socket_create(AF_INET, SOCK_STREAM, 0))) {
            $errorCode = socket_last_error();
            $errorMessage = socket_strerror($errorCode);
            throw new SocketCreationError($errorCode, $errorMessage);
        }
        return $socket;
    }

    /**
     * @param $socketToBind
     * @param $host
     * @param $port
     * @throws SocketBindingError
     */
    public function bindSocket($socketToBind, $host, $port)
    {
        if (!socket_bind($socketToBind, $host, $port)) {
            $errorCode = socket_last_error();
            $errorMessage = socket_strerror($errorCode);
            throw new SocketBindingError($errorCode, $errorMessage);
        }
    }

    /**
     * @param $socketToListen
     * @throws SocketListeningError
     */
    public function listenSocket($socketToListen)
    {
        if (!socket_listen($socketToListen, 10)) {
            $errorCode = socket_last_error();
            $errorMessage = socket_strerror($errorCode);
            throw new SocketListeningError($errorCode, $errorMessage);
        }
    }
}
