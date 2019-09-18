<?php

define('DIR_APP', dirname(__DIR__));

require_once(DIR_APP . '/Classes/Server.php');

class MessageBroker
{
    private static $instance;

    private function __construct()
    {
        //The constructor is made private to disable intantiation
    }

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new Server("127.0.0.1", 5000);
        }

        return static::$instance;
    }

    /**
     * @throws Exception
     */
    final public function __clone()
    {
        //We cannot create a clone of our MessageBroker instance as it should be the only one
        //We made this function final so that it can't be overridden
        throw new Exception('Feature disabled!');
    }

    /**
     * @throws Exception
     */
    final public function __wakeup()
    {
        //Avoid multiple different deserialization which leads to multiple instances of MessageBroker
        //We made this function final so that it can't be overridden
        throw new Exception('Feature disabled!');
    }
}
