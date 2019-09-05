<?php
namespace Core;

use Common\Router;
use Noahbuscher\Macaw;
use Pimple\Container;

class Bootstrap
{
    //引导程序
    public function __construct(){
        $this->autoload();
        $this->buildDI();
        $this->importRoute();
    }

    //启动运行
    public function run(){
        Macaw::dispatch();
    }

    //自动加载
    public function autoload() {
        spl_autoload_register(function($class){
            $class = strtr($class, '\\', DS);
            $filename = basename($class);
            $filedir  = strtolower(dirname($class));
            $filepath = APP_DIR . DS . $filedir . DS . $filename . EXT;
            $libpath = LIB_DIR.DS.$filedir.DS.$filename.EXT;

            if (file_exists($filepath)){
                require_once($filepath);
            } else if (file_exists($libpath)){
                require_once($libpath);
            } else {
                echo $filepath.BR;
                trigger_error("Unable to load class: $class", E_USER_WARNING);
            }
        },true, true);
    }

    //依赖注入
    public function buildDI(){
        global $_DI;
        $container = new Container();
        $provider = new Provider();
        $provider->register($container);
        $_DI = $container;
    }


    //导入路由
    public function importRoute(){
        foreach (Router::config as $route){
            $method = $route[0];
            $path = $route[1];
            $action = $route[2];

        
            $controller = ucfirst(basename($path));
            $module = ucfirst(ltrim(dirname($path), '/'));
            $action = "Module\\$module\\Controller\\$controller@$action";

            Macaw::$method($path, $action);
        }
    }

}




