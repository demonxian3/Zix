<?php
return [

    'curl' => [
        'dynamic' => false,
        'class'   => '\\Khazix\\Curl',
        'extend'  => false,
        'params'  => [ '@timeout', '@certs' ],
    ],

    'mysql' => [
        'dynamic' => false,
        'class'   => '\\Catfan\\Medoo',
        'params'  => [
            [
                'database_type' => 'mysql',
                'server'        => 'localhost',
                'database_name' => '@database',
                'username'      => '@username',
                'password'      => '@password',
            ],
        ],
    ],

    'redis' => [
        'dynamic' => false,
        'class'   => '\\Redis',
        'params'  => [],
        'extend'  => function($obj, $di) {
            $config = $di['config']->get('redis');
            $host = $config['host'];
            $port = $config['port'];
            $obj->connect($host, $port);
            return $obj;
        },
    ],

    'wxaccount' => [
        'dynamic' => false,
        'class'   => '\\Khazix\\Wxaccount',
        'params'  => [ '@token', '@appid', '@appsecret' ],
        'extend'  => function($obj, $di) {
            $obj->setContainer($di);
            return $obj;
        },
    ],

    'sms' => [
        'dynamic' => false,
        'class'   => '\\Khazix\\Sms',
        'params'  => ['@appid', '@appkey', '@tplid'],
        'extend'  => function($obj, $di) {
            $obj->setContainer($di);
            return $obj;
        },
    ],

    'logger_formatter' => [
        'dynamic' => true,
        'class'   => '\\Monolog\\Formatter\\LineFormatter',
        'params'  => [ '@format', '@dateFormat' ],
    ],

    'logger_handler' => [
        'dynamic' => true,
        'class'   => '\\Monolog\\Handler\\FileHandler',
        'params'  => [ '@filepath', '@level' ],
        'extend'  => function($obj, $di) {
            $obj->setFormatter($di['logger_formatter']);
            return $obj;
        },
    ],

    'logger' => [
        'dynamic' => true,
        'class'   => '\\Monolog\\Logger',
        'params'  => [ '@channel' ],
        'extend'  => function($obj, $di) {
            $obj->pushHandler($di['logger_handler']);
            return $obj;
        },
    ]

];
