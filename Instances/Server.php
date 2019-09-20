<?php

define('DIR_APP', dirname(__DIR__));

require_once(DIR_APP . "/Classes/MessageBroker.php");
require_once(DIR_APP . "/Exceptions/SocketCreationError.php");
require_once(DIR_APP . "/Exceptions/SocketBindingError.php");
require_once(DIR_APP . "/Exceptions/SocketListeningError.php");

error_reporting(E_ERROR | E_PARSE);
set_time_limit(0);

/** @var Server $messageBroker */
$messageBroker = MessageBroker::getInstance();

try {
    $socket = $messageBroker->createSocket();
} catch (SocketCreationError $socketCreationError) {
    die("Could not create socket: " . $socketCreationError->getErrorCode() . ' ' . $socketCreationError->getMessage() . "\n");
}
echo "Socket successfully created! \n";

try {
    $messageBroker->bindSocket($socket, $messageBroker->getHost(), $messageBroker->getPort());
} catch (SocketBindingError $socketBindingError) {
    die("Could not bind socket: " . $socketBindingError->getErrorCode() . ' ' . $socketBindingError->getMessage() . "\n");
}
echo "Socket successfully binded! \n";

try {
    $messageBroker->listenSocket($socket);
} catch (SocketListeningError $socketListeningError) {
    die("Could not listen on socket: " . $socketListeningError->getErrorCode() . ' ' . $socketListeningError->getMessage() . "\n");
}
echo "Socket is listening!\n";
echo "Waiting for incoming connections...\n\n";

$clientSockets = array();
$socketsToRead = array();

while (true) {
    $socketsToRead = array();
    $socketsToRead[0] = $socket;

    for ($i = 0; $i < $messageBroker->getMaxClients(); $i++) {
        if ($clientSockets[$i] != null) {
            $socketsToRead[$i + 1] = $clientSockets[$i];
        }
    }

    if (socket_select($socketsToRead, $write, $except, null) === false) {
        $errorCode = socket_last_error();
        $errorMessage = socket_strerror($errorCode);

        die("Could not listen on socket: [$errorCode] $errorMessage \n");
    }

    if (in_array($socket, $socketsToRead)) {
        for ($i = 0; $i < $messageBroker->getMaxClients(); $i++) {
            if ($clientSockets[$i] == null) {
                $clientSockets[$i] = socket_accept($socket);

                if (socket_getpeername($clientSockets[$i], $host, $port)) {
                    echo "Client $host:$port is now connected to this server!\n\n";
                }

                $welcomeMessage = "Welcome!!! Enter a message and press enter to send!";
                $welcomeMessage .= " Type EXIT for closing the connection...\n\n";
                socket_write($clientSockets[$i], $welcomeMessage);
                break;
            }
        }
    }

    for ($i = 0; $i < $messageBroker->getMaxClients(); $i++) {
        if (in_array($clientSockets[$i], $socketsToRead)) {
            $clientInput = socket_read($clientSockets[$i], 1024);

            if (trim($clientInput) === 'EXIT') {
                if (socket_getpeername($clientSockets[$i], $host, $port)) {
                    echo "Client: $host:$port disconnected!\n";
                }
                unset($clientSockets[$i]);
                socket_close($clientSockets[$i]);
            }

            $response = "Your message: " . "'" . trim($clientInput) . "'" . " was successfully received!\n\n";

            if (socket_getpeername($clientSockets[$i], $host, $port)) {
                echo "Current client: $host:$port\n";
            }
            echo "Sending the response message to client...\n\n";
            socket_write($clientSockets[$i], $response);
        }
    }
}
