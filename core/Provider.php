<?php
namespace Core;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class Provider implements ServiceProviderInterface
{
    private $_di;

    private $configLoader;

    public function __construct(ConfigLoader $configLoader)
    {
        $this->services = $configLoader->get('service');

        $this->configLoader = $configLoader;
    }
    
    public function register(Container $di)
    {
        $this->_di = $di;

        $this->registerConfigLoader();

        $this->registerParamsProcessor();

        $this->registerAllServices();
    }

    //注册配置加载器
    public function registerConfigLoader() 
    {
        $loader = $this->configLoader;

        $this->_di['config'] = function($di) use($loader) {
            return $loader;
        };
    }

    //注册参数解析函数，@开头的参数会读取配置文件
    public function registerParamsProcessor()
    {
        $this->_di['__function_parse_params'] = $this->_di->protect(
            function ($name, $params) {
                $config = $this->_di['config']->get($name);

                $result = [];
                foreach ($params as $param) {

                    if (is_string($param) && $param[0] === '@') {
                        $key = substr($param, 1);
                        $param = $config[$key] ?? $param;

                    } else if(is_array($param)) {
                        foreach($param as $k => $v) {
                            if (is_string($v) && $v[0] === '@') {
                                $try = substr($v, 1);
                                $param[$k] = $config[$try] ?? $v;
                            }
                        }
                    }
                    array_push($result, $param);
                }

                return $result;
            }
        );
    }

    //按顺序注册所有配置表列出的服务, params也要按顺序
    public function registerAllServices()
    {
        foreach ($this->services as $name => $option) {

            $params = [];

            $class = $option['class'];

            if ($option['dynamic'] === false) {

                $params = $this->_di['__function_parse_params']($name, $option['params']);

                $this->registerStatic($name, $class, $params);
            } else {
                $this->registerDynamic($name, $class, $option['params']);
            }


            if (isset($option['extend']) && is_callable($option['extend'])) {
                $this->_di->extend($name, $option['extend']);
            }
        }

    }

    public function registerStatic($name, $class, $params)
    {
        $this->_di[$name] = function($di) use ($class, $params){
            return new $class(...$params);
        };
    }

    public function registerDynamic($name, $class, $params)
    {
        $this->_di[$name] = $this->_di->factory(
            
            function ($di) use($name, $class, $params) {

                $dynamic = $di['__function_parse_params']($name, $params);

                return new $class(...$dynamic);
            }
        );
    }

}
