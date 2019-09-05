<?php
namespace Core;

use Common\Config;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class Provider implements ServiceProviderInterface
{
    protected static $classMap = [
        'curl'              => '\\Khazix\\Curl',
        'mysql'             => '\\Catfan\\Medoo',
        'redis'             => '\\Redis',
        'wxaccount'         => '\\Khazix\\Wxaccount',
        'logger'            => '\\Monolog\\Logger',
        'logger_handler'    => '\\Monolog\\Handler\\FileHandler',
        'logger_formatter'  => '\\Monolog\\Formatter\\LineFormatter',
    ];
    
    public function register(Container $di)
    {
        $this->_di = $di;
        $this->_di['di_class_map'] = static::$classMap;

        $this->init('curl');
        $this->init('mysql');
        $this->init('redis');
        $this->init('wxaccount');
        $this->init('logger');
    }

    public function init($server)
    {
        $this->$server();
    }

    public function registerDynamicServer($name, $dynamic_params)
    {
        $class = static::$classMap[$name];


        $keys = array();
        foreach ($dynamic_params as $key => $value) {
            $this->_di[$key] = $value;
            array_push($keys, $key);
        }

        $this->_di[$name] = $this->_di->factory(function ($di) use($class, $keys) {

            $dynamic_params = [];
            foreach ($keys as $key){
                if ($this->_di[$key]){
                    array_push($dynamic_params, $this->_di[$key]);
                }
            }

            return new $class(...$dynamic_params);
        });
    }

    public function registerStaticServer($name, $params)
    {
        $class = static::$classMap[$name];

        $this->_di[$name] = function($di) use ($class, $params){
            return new $class(...$params);
        };
    }

    public function extendServer($name, $call)
    {
        $this->_di->extend($name, $call);
    }


    public function curl()
    {
        $config = Config::curl;

        $params = [
            $config['timeout'], 
            $config['certs']
        ];

        $this->registerStaticServer('curl', $params);
    }


    public function mysql()
    {
        $config = Config::mysql;

        $params = [[
            'database_type' => 'mysql',
            'server'        => 'localhost',
            'database_name' => $config['database'],
            'username'      => $config['username'],
            'password'      => $config['password'],
        ]];

        $this->registerStaticServer('mysql', $params);
    }

    public function redis()
    {
        $config = Config::redis;

        $params = [];

        $this->registerStaticServer('redis', $params);

        $this->extendServer('redis', function ($obj, $di) use($config) {
            $obj->connect($config['host'], $config['port']);
            return $obj;
        });

    }

    public function wxaccount()
    {
        $config = Config::wxaccount;
    
        $params = [
            $config['token'],
            $config['appid'],
            $config['appsecret'],
        ];

        $this->registerStaticServer('wxaccount', $params);

        $this->extendServer('wxaccount', function ($obj, $di) {
            $obj->setDI($di);
            return $obj;
        });
    }

    public function loggerHandler()
    {
        $params = [
            'logger_filepath' => Config::logger['filepath'],
            'logger_level' => Config::logger['level'],
        ];

        $this->registerDynamicServer('logger_handler', $params);

        $this->init('loggerFormatter');

        $this->extendServer('logger_handler', function ($obj, $di) {
            $obj->setFormatter($di['logger_formatter']);
            return $obj;
        });
    }

    public function loggerFormatter()
    {
        $params = [
            'logger_format' => Config::logger['format'],
            'logger_date_format' => Config::logger['dateFormat'],
        ];

        $this->registerDynamicServer('logger_formatter', $params);
    }


    public function logger()
    {
        $params = [
            'logger_channel' => Config::logger['channel'],
        ];

        $this->registerDynamicServer('logger', $params);

        $this->init('loggerHandler');

        $this->extendServer('logger', function ($obj, $di) {
            $obj->pushHandler($di['logger_handler']);
            return $obj;
        });

    }

    
//use Monolog\Logger;
//use Monolog\Handler\FileHandler;
//use Monolog\Formatter\LineFormatter;

//$handler = new FileHandler($logpath);
//
//$dateFormat = 'Y-m-d H:i:s';
//$output = "[%datetime%] %channel%.%level_name%  :%message%\n%context%\n\n";
////$formatter = new LineFormatter($output, $dateFormat);
////$stream->setFormatter($formatter);
//echo '<pre>';
//$logger = new Logger('public test');
//$logger->pushHandler($handler);
//
//
//$data = ['fucking u'];
//
//$logger->info('Adding a new user', $data);


}
