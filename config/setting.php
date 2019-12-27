<?php

return [

    'mysql' => [
        'host'     => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'database' => 'bds',
    ],

    'redis' => [
        'host' => 'localhost',
        'port' => '6379',
    ],

    'wxaccount' => [
        'token' => 'your_token',
        'appid' => 'your_appid',
        'appsecret' => 'your_appsecret',
    ],

    'curl' => [
        'timeout' => 3,
        'certs'=>[
            'ca' => '/path/to/ca.pem',
            'cert' => '/path/to/cert.pem',
            'key' => '123456',
        ]
    ],

    'sms' => [
        'appid' => 'your_appid',
        'appkey' => 'your_appkey',
        'tplid' => '123456',
    ],

    'logger' => [
        'channel' => 'zix',
    ],

    'logger_handler' => [
        'filepath' => DATA_DIR.DS.'/log/wechat.log',
        'level' => \Monolog\Logger::DEBUG,
    ],

    'logger_formatter' => [
        'format' => "[%datetime%] %channel%.%level_name%: %message%\n%context%\n%extra%\n%variables%\n\n",
        'dateFormat' => "Y-m-d H:i:s",
    ],


];


