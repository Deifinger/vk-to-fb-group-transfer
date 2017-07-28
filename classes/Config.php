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

        $configPath = dirname(dirname(__FILE__)) . "/config.php";

        if(file_exists($configPath))
        {
            require ($configPath);
            $this->config = &$config;
        }
        else
        {
            $this->config = array();
        }
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
        $config = self::getInstance()->getConfig();

        $keys = array($key);
        if(strpos($key, '.') !== false)
        {
            $keys = explode('.', $key);
        }

        $count = sizeof($keys);
        $buff = null;
        for($i = 0; $i < $count; $i++)
        {
            // if buffer is null
            if($buff == null)
            {
                $ref = &$config; // get ref of config
            }
            else
            {
                $ref = &$buff; // or get ref of buffer
            }

            // if it's last item
            if($i === $count - 1)
            {
                $buff = $ref[$keys[$i]]; // get value
            }
            else
            {
                $buff = &$ref[$keys[$i]]; // if not get ref
            }
        }

        return $buff;
    }


}