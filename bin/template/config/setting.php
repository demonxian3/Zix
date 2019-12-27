<?php

return [
    /** Lib */
    'app' => [
        'baseUrl' => 'http://www.your_domain.com',
    ],



    'curl' => [
        'timeout' => 3,
        'certs'=>[
            'ca' => '/path/to/ca.pem',
            'cert' => '/path/to/cert.pem',
            'key' => '123456',
        ]
    ],

    /** Model */
    'mysql' => [
        'host'     => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'database' => 'database',
    ],

    'redis' => [
        'host' => 'localhost',
        'port' => '6379',
    ],

    /** Wechat */
    'wxaccount' => [
        'token' => 'your_token',
        'appid' => 'your_appid',
        'appsecret' => 'your_appsecret',
    ],

    'menu' => [
        'button' => [
            [
                'name' => '多级目录',
                'sub_button' => [
                    [
                        'name' => 'mdn',
                        'url' => 'http://www.mdn.com',
                        'type' => 'view',
                    ],
                ]
            ],
            [
                'name' => '一级目录',
                'type' => 'view',
                'url' => 'http://www.google.com',
            ],
        ]
    ],

    /** SDK */
    'sms_tencent' => [
        'appid' => 'your_appid',
        'appkey' => 'your_secret',
        'tplid' => 'your_tplid',
    ],

    'sms_aliyun' => [
        'signName' => 'your_sign_name',
        'templateCode' => 'your_template_code',
        'accessKeyId' => 'your_access_key',
        'accessSecret' => 'your_access_secret',
    ],

    /** Logger */
    'logger' => [
        'channel' => 'zix',
    ],

    'logger_handler' => [
        'filepath' => DATA_DIR.DS.'/log/app.log',
        'level' => \Monolog\Logger::DEBUG,
    ],

    'logger_formatter' => [
        'format' => "[%datetime%] %channel%.%level_name%: %message%\n%context%\n%extra%\n%variables%\n\n",
        'dateFormat' => "Y-m-d H:i:s",
    ],
];


