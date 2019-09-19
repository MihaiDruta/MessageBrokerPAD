<?php

define('DIR_APP', dirname(__DIR__));

require_once(DIR_APP . "/Classes/MessageBroker.php");

error_reporting(E_ERROR | E_PARSE);
set_time_limit(0);

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
echo "Waiting for incoming connections...\n\n";

//array of client sockets
$clientSockets = array();

//array of sockets to read
$socketsToRead = array();

//loop for listening for incoming connections and process existing ones
while (true) {
    //preparing the array of readable client sockets
    $socketsToRead = array();

    //first socket will be the master socket
    $socketsToRead[0] = $socket;

    //adding all the existing client sockets
    for ($i = 0; $i < $messageBroker->getMaxClients(); $i++) {
        if ($clientSockets[$i] != null) {
            $socketsToRead[$i + 1] = $clientSockets[$i];
        }
    }

    //calling socket_select which is a blocking call
    if (socket_select($socketsToRead, $write, $except, null) === false) {
        $errorCode = socket_last_error();
        $errorMessage = socket_strerror($errorCode);

        die("Could not listen on socket: [$errorCode] $errorMessage \n");
    }

    if (in_array($socket, $socketsToRead)) {
        for ($i = 0; $i < $messageBroker->getMaxClients(); $i++) {
            if ($clientSockets[$i] == null) {
                $clientSockets[$i] = socket_accept($socket);

                //display information about the client who is connected
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

    //checking each client for incoming messages
    for ($i = 0; $i < $messageBroker->getMaxClients(); $i++) {
        if (in_array($clientSockets[$i], $socketsToRead)) {
            $clientInput = socket_read($clientSockets[$i], 1024);

            //if a client sends an exit message the socket is going to be closed
            if (trim($clientInput) === 'EXIT') {
                if (socket_getpeername($clientSockets[$i], $host, $port)) {
                    echo "Client: $host:$port disconnected!\n\n";
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
