<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/27/17 4:51 PM
 */

namespace VKToFB;


class Config
{
    private static $instance = null;
    private $config = null;

    private function __construct()
    {
        // TODO: rewrite config file for getting all config
        // TODO: immediately from file to variable
        require "./../config.php";
        $this->config = &$config;
    }

    public static function getInstance()
    {
        if(self::$instance === null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function getConfig()
    {
        return self::getInstance()->config;
    }

    public static function get(string $key)
    {
        $config = self::getInstance();

        $keys = $key;
        if(strpos($key, '.') !== false)
        {
            $keys = explode('.', $key);
        }

        $count = sizeof($keys);
        $buff = null;
        for($i = 0; $i < $count; $i++)
        {
            if($buff == null)
            {
                $buff = &$config[$keys[$i]];
            }
            else
            {
                $buff = &$buff[$keys[$i]];
            }
        }

        return $buff;
    }


}