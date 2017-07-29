<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/28/17 10:46 PM
 */

use VKToFB\Config;
use VKToFB\Logger;

// TODO: delete than rewrite all dependencies
require 'config.php';

session_start();

$logger = Logger::getInstance();
$logStream = new \Monolog\Handler\StreamHandler(Config::get('logFile'));
$logger->addStreamHandler($logStream);

