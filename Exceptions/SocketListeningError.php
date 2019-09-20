<?php

require_once(DIR_APP . '/Exceptions/SocketException.php');

class SocketListeningError extends SocketException
{
    /** @var int */
    private $errorCode;

    public function __construct($errorCode, $message)
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
