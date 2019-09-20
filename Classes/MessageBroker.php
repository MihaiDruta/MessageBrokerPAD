<?php

require_once(DIR_APP . '/Classes/Server.php');

class MessageBroker
{
    private static $instance;

    private function __construct() {}

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new Server("127.0.0.1", 5000, 10);
        }

        return static::$instance;
    }

    /**
     * @throws Exception
     */
    final public function __clone()
    {
        throw new Exception('Feature disabled!');
    }

    /**
     * @throws Exception
     */
    final public function __wakeup()
    {
        throw new Exception('Feature disabled!');
    }
}
