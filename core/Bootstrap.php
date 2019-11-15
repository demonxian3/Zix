<?php
namespace Core;

use Noahbuscher\Macaw;
use Pimple\Container;

class Bootstrap
{
    private $configLoader;

    //引导程序
    public function __construct(){
        $this->checkVersion();
        $this->initAutoloader();
        $this->initConfigLoader();
        $this->initProvider();
        $this->initRouter();
    }

    //启动运行
    public function run()
    {
        Macaw::dispatch();
    }

    //检测版本
    public function checkVersion()
    {
        if (version_compare(PHP_VERSION, '7.1', '<')) {

            trigger_error("PHP version at least 7.1 or more, current version is ".PHP_VERSION, E_USER_ERROR);
        }
    }

    //自动加载器
    public function initAutoloader() 
    {
        spl_autoload_register(function($class){
            $class = strtr($class, '\\', DS);
            $filename = basename($class);
            $filedir  = strtolower(dirname($class));
            $filepath = APP_DIR .DS. $filedir .DS. $filename . EXT;
            $libpath  = LIB_DIR .DS. $filedir .DS. $filename . EXT;

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

    //配置加载器
    public function initConfigLoader()
    {
        $env = getenv('APP_ENV');

        $this->configLoader = new ConfigLoader($env);
    }

    //依赖加载器
    public function initProvider()
    {
        global $_DI;
        $container = new Container();
        $provider = new Provider($this->configLoader);

        $provider->register($container);
        $_DI = $container;
    }

    //路由加载器
    public function initRouter()
    {
        $routings = $this->configLoader->get('routing');

        foreach ($routings as $route){
            $method = $route[0];
            $path = $route[1];
            $action = $route[2];

            //remove (:num) (:any) (:all) from class path
            $classpath = preg_replace('#/\(.*?\)#', '', $route[1]);

            $controller = ucfirst(basename($path));
            $module = ucfirst(ltrim(dirname($path), '/'));
            $action = "$module\\Controller\\$controller@$action";

            Macaw::$method($path, $action);
        }
    }

}




