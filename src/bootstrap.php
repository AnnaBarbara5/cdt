<?php

declare(strict_types=1);

use App\Config;
use App\Security\SessionManager;

require dirname(__DIR__) . '/vendor/autoload.php';

Config::load();

$debug = Config::isDebug();
ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');
error_reporting(E_ALL);

SessionManager::start();