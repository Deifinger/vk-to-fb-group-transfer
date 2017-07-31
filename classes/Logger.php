<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/27/17 4:39 PM
 */

namespace VKToFB;


use Monolog\Logger as MonoLogger;
use Monolog\Handler\StreamHandler;

class Logger
{
    private static $instance = null;
    private static $logger = null;

    private function __construct()
    {
        self::$logger = new MonoLogger(__NAMESPACE__);

    }

    public static function getInstance()
    {
        if(self::$instance == null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function getLogger()
    {
        return self::$logger;
    }

    public function addStreamHandler(StreamHandler $handler)
    {
        self::$logger->pushHandler($handler);
    }

}