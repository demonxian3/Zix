<?php

ini_set('display_errors', true);
header("Content-Type: text/html;charset=utf-8");

define('CR', PHP_EOL);
define('BR', "<br>");
define('DS', DIRECTORY_SEPARATOR);
define('EXT', '.php');

define('APP_DIR', dirname(__DIR__));
define('LIB_DIR', APP_DIR.DS.'lib');
define('DATA_DIR', APP_DIR.DS.'data');
define('CORE_DIR', APP_DIR.DS.'core');
define('CONFIG_DIR', APP_DIR.DS.'config');

require_once(CORE_DIR.DS.'Bootstrap.php');

$bootstrap = new \Core\Bootstrap;

$bootstrap->run();

