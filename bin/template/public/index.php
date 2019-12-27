<?php
$lib = getenv('ZIX_HOME');
if (!$lib) {
    echo 'Please set environment variable "ZIX_HOME" to point to zix path first';
    exit;
}

define('ZIX_DIR', $lib);
ini_set('display_errors', true);
header("Content-Type: text/html;charset=utf-8");
ini_set("date.timezone", "Asia/Shanghai");

define('CR', PHP_EOL);
define('BR', "<br>");
define('DS', DIRECTORY_SEPARATOR);
define('EXT', '.php');

define('APP_DIR', dirname(__DIR__));
define('LIB_DIR', ZIX_DIR.DS.'lib');
define('CORE_DIR', ZIX_DIR.DS.'core');
define('DATA_DIR', APP_DIR.DS.'data');
define('CONFIG_DIR', APP_DIR.DS.'config');
require_once(CORE_DIR.DS.'Bootstrap.php');

echo '<pre>';

$bootstrap = new \Core\Bootstrap;
$bootstrap->run();

