<?php

require dirname(__DIR__).'/vendor/autoload.php';

// TODO Use Dotenv to load configuration

$_SERVER += $_ENV;
$_SERVER['APP_ENV'] = DEBUG ? 'dev' : 'prod';
$_SERVER['APP_DEBUG'] = $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? 'prod' !== $_SERVER['APP_ENV'];
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = (int) $_SERVER['APP_DEBUG'] || filter_var($_SERVER['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
