<?php

namespace common;

class Config
{
    public const mysql = [
        'host'     => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'database' => 'mysql',
    ];

    public const redis = [
        'host' => 'localhost',
        'port' => '6379',
    ];

    public const wxaccount = [
        'token' => 'your token',
        'appid' => 'your appid',
        'appsecret' => 'your appsecret',
    ];

    public const curl = [
        'timeout' => 3,
        'certs'=>[
            'ca' => '/path/to/ca.pem',
            'cert' => '/path/to/cert.pem',
            'key' => 'cert key',
        ]
    ];

    public const logger = [
        'level' => \Monolog\Logger::DEBUG,
        'filepath' => DATA_DIR.DS.'/log/wechat.log',
        'channel' => 'zix',
        'format' => "[%datetime%] %channel%.%level_name%: %message%\n%context%\n%extra%\n%variables%\n\n",
        'dateFormat' => "Y-m-d H:i:s",
    ];
}
