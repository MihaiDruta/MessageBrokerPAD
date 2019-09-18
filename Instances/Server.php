<?php

define('DIR_APP', dirname(__DIR__));

require_once(DIR_APP . "/Classes/MessageBroker.php");

//creating a MessageBroker instance if it is not yet created
/** @var Server $messageBroker */
$messageBroker = MessageBroker::getInstance();

//creating socket for server
if (!($socket = socket_create(AF_INET, SOCK_STREAM, 0))) {
    $errorCode = socket_last_error();
    $errorMessage = socket_strerror($errorCode);

    die("Could not create socket: [$errorCode] $errorMessage \n");
}

echo "Socket successfully created! \n";

//binding te socket to a particular host and port
if (!socket_bind($socket, $messageBroker->getHost(), $messageBroker->getPort())) {
    $errorCode = socket_last_error();
    $errorMessage = socket_strerror($errorCode);

    die("Could not bind socket: [$errorCode] $errorMessage \n");
}

echo "Socket successfully binded! \n";

//start listening for connections
if (!socket_listen($socket, 10)) {
    $errorCode = socket_last_error();
    $errorMessage = socket_strerror($errorCode);

    die("Could not listen on socket: [$errorCode] $errorMessage \n");
}

echo "Socket is listening!\n";
echo "Waiting for incoming connections...\n";

//accepting incoming connection
$client = socket_accept($socket);

//display information about the client who is connected
if (socket_getpeername($client, $host, $port)) {
    echo "Client $host:$port is now connected to this server!\n";
}

socket_close($client);
socket_close($socket);
