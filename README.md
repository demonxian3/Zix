# zix框架 v1.3.3    

一款轻量级，针对微信公众号，微信小程序，微信页，微信支付等后台封装开发框架，    

采用 vue + api框架模式前后端完全分离。  

## 主要特性 

- 自动加载  
- 路由  
- 扁平化    
- ORM操作   
- 依赖注入  
- 日志系统  
- 可配置    
- 模型和控制器  
- 自定化程度高  
- 封装微信相关API   

## 未来趋势 

加入 OAuth2.0 鉴权系统  

完善控制器和模型的抽象  

通过 `Yaf` `Phalcon`  框架性能加速  


## 目录结构 

``` 
├── common                                  公共    
│   ├── BaseController.php                      基础控制器  
│   └── MysqlBaseModel.php                      基础模型    
├── config                                  配置    
│   ├── development                             开发环境配置目录    
│   │   └── setting.php                             
│   ├── routing.php                             路由表  
│   ├── service.php                             服务注册表  
│   └── setting.php                             配置表  
├── core                                    核心    
│   ├── Bootstrap.php                           引导文件    
│   ├── ConfigLoader.php                        配置加载器  
│   └── Provider.php                            依赖加载器  
├── data                                    数据    
│   ├── log                                     日志    
│   └── upload                                  
├── lib                                     类库    
│   ├── catfan                                  ORM 
│   ├── khazix                                  自制类  
│   │   ├── Curl.php                                Curl请求封装    
│   │   ├── JWT.php                                 JWT鉴权封装 
│   │   ├── Request.php                             用户交互数据封装    
│   │   ├── Sms.php                                 短信API 
│   │   ├── Utils.php                               工具类  
│   │   └── Wxaccount.php                           微信公众号API   
│   ├── monolog                                 日志    
│   ├── noahbuscher                             路由    
│   ├── pimple                                  依赖注入    
│   └── psr                                     PSR标准 
│       ├── container   
│       └── log 
├── module                                  模块    
│   └── manage                                  模块名  
│       ├── controller                              控制器  
│       ├── model                                   模型    
│       └── view                                    视图    
├── public                                  开放    
│   └── index.php                               入口文件    
├── README.md   
``` 

## 安装部署 

### 环境    

- [PHP7.x](https://www.php.net/downloads.php)       

- [Redis 4.0.6](https://redis.io/download)      

- [phpredis](https://github.com/phpredis/phpredis)  

### 配置示例    

- nginx 

``` 
server {    
    listen       80;    
    server_name  www.example.com    
    charset UTF-8;  
    location / {    
        root   /var/www/html/app/;  
        index  index.php index.html;    
    }   
    if ($request_uri !~ (/view/|/test/)  ){ 
        rewrite ^(.*)$ /public/index.php break; 
    }   
    error_page   500 502 503 504  /50x.html;    
    location = /50x.html {  
        root   /var/www/html/app/;  
    }   
    location ~ \.php$ { 
        root           /var/www/html/app/public/;   
        fastcgi_pass   127.0.0.1:9000;  
        fastcgi_index  index.php;   
        fastcgi_param  SCRIPT_FILENAME  /var/www/html/app/$fastcgi_script_name; 
        include        fastcgi_params;  
    }   
}   
``` 

- php.ini   

``` 
extension=/usr/lib64/php/modules/redis.so   
``` 

## 框架文档 

### 开发规范    

整体规范遵循 [PSR](http://psr.phphub.org/) 标准 

路由规范遵循 [RESTful API](http://www.ruanyifeng.com/blog/2018/10/restful-api-best-practices.html) 风格 


- 命名规范  

类文件名(filename), 空间名(namespace), 类名(class) 一致性参考 PSR-4 ，首字母均大写，其他目录名和普通文件均小写  


- 编码规范  

数据库，前后端，缓存等统一为 `UTF-8` 编码，时区设置为标准时间 `UTC` 

### 引导流程    

入口文件定义一些路径常量，然后引入 bootstrap 程序来引导框架，分以下步骤:    

1. 检查版本是否大于7.1  
2. 初始化自动加载器     
3. 初始化配置加载器     
4. 初始化依赖加载器     
5. 初始化路由加载器     

这些过程可在 `Core\Bootstrap->__construct()` 看到   


### 版本检测    

如果PHP版本小于 7.1 将无法运行该框架，会报错    

``` 
Fatal error: PHP version at least 7.1 or more, current version is 7.2.19 in /var/www/html/sxsh/core/Bootstrap.php on line 32    
``` 

### 自动加载    

- 加载规则  

当使用`use`引入对象时，根据`PSR-4`一致性原则，通过`namespace`寻找类对应的文件路径并尝试引入，   

在引入失败情况下，会到 `lib/` 目录下进行尝试引入，再失败的情况则抛出异常.   

- 加载示例  

1. 下面的代码会根据PSR-4一致性原则引入`namespace`对应文件路径的类文件   

``` php 
use Module\Manager\Controller\Goods;    
``` 

将引入      

``` php     
require_once('module/manage/controller/goods.php'); 
``` 

2. 下面的代码会尝试到 `lib` 下根据`namespace`作为相对路径寻找类文件 

``` php 
use Khazix\Curl;    
``` 

将引入  

``` php 
require_once('lib/khazix/curl.php');    
``` 

3. 下面的情况会抛出异常 

``` 
use NoExist\ClassName;  
``` 

将抛出  

``` php 
trigger_error("Unable to load class: $class", E_USER_WARNING);  
``` 

### 配置加载    

#### 概述   

配置文件均以 `return array` 的php文件来描述 

配置加载器: `core/ConfigLoader`     
配置数据目录:  `config/`        
生产环境配置文件:  `config/setting.php`     
开发环境配置文件:  `config/development/setting.php` 

可配置项:   

1. 程序配置: setting.php        
2. 路由配置: routing.php        
3. 服务配置: service.php        

默认情况下，会使用生产环境的配置，如果需要使用开发环境的配置，需要在命令行设置环境变量: 

``` sh  
set APP_ENV='development';  
``` 

#### 说明   

因为容器注册服务时，有时需要读取服务对应的配置，因此首先将 config 作为服务注册到容器中  

读取配置可用依赖注入的形式进行读取，extend扩展时也很方便的获取到 config     

```php  
echo $_DI['config']->get('mysql')['database'];  
``` 

#### 路由配置:  

规则    

``` php 
[method, pathinfo, action], 
``` 

示例    

``` php 
['post', '/wxaccount/menu', 'createMenu'],  
``` 

读取路由表  

``` php 
(new \Core\ConfigLoader())->get('routing'); //array 
``` 

#### 程序配置:  

规则    

```     
[ name => options ],    
``` 

示例    

``` php 
'mysql' => [    
    'host'     => 'localhost',  
    'username' => 'root',   
    'password' => 'root',   
    'database' => 'test',   
],  
``` 

读取配置信息    

``` php 
(new \Core\ConfigLoader())->get('mysql'); //array   
``` 

#### 服务配置:  

概述    

服务配置：指的是以配置文件形式描述一些需要以服务的形式注册到容器中的类，    

将来可在其他类需要这些使用这些服务时进行依赖注入。  

规则    


``` 
'service_name' => [ 
    'dynamic' => (bool) true,   
    'class'   => (string) namespace,    
    'params'  => (array) construct_sequence_params, 
    'extend'  => (callable) function($obj, $dj) {}  
]   
``` 

说明    

- service\_name 

服务名，读取配置文件时会根据服务名来选择相应的配置，使用容器的服务也跟据此名称。    

- dynamic   

为 `true` 时使用动态注册，否则使用静态注册  

静态注册：每次从容器里获取的都是同一个对象，只有首次使用才会实例化一个对象，其他均引用该对象    

动态注册：每次从容器里读取服务时，都会实例化出一个不同的对象，因此可以设置不同初始化参数    


- class 

指明服务类的域名空间路径，如 '\\Khazix\\Curl' 注意需要双斜杠转义    

- params    

初始化参数，会以 `new $class(...$params)` 方式传给 `__construct`    

因此请确保 $params 每个参数的顺序和对象实例化构造参数的顺序一致 

如果需要读取配置文件，可以在前面加上 @ 符号就会尝试读取配置文件 

如果读取失败，将传递原来的指，解析读取配置通过容器用 protect 注 

册的函数`__function_parse_params` 来实现, 稍后会通过例子来说明  


- extend    

扩展匿名函数，对已经初始化后的服务进行扩展，如 redis 的初始连接，具体参考 [pimple文档](https://packagist.org/packages/pimple/pimple)    

需要读取配置的化，可以在 `$di['config']` 中获取 

``` php 
'redis' => [    
    'extend' => function($obj, $di) {   
        $config = $di['config']->get('redis');  
        $obj->connect($config['host'], $config['port']);    
        return $obj;    
    };  
]   
``` 

示例    

``` 
'mysql' => [    
    'dynamic' => false, 
    'class'   => '\\Catfan\\Medoo', 
    'params'  => [  
        [   
            'database_type' => 'mysql', 
            'server'        => 'localhost', 
            'database_name' => '@database', 
            'username'      => '@username', 
            'password'      => '@this_is_pwd',  
        ],  
    ],  
],  
``` 

如示例所示采用静态注册，注册服务名为 `mysql`，类的路径为`\\Catfan\\Medoo`   

初始化参数中，`database_name` `username` `password` 这三个参数值开头有@符号 

解析器将会尝试读取: 

``` 
$_DI['config']->get('mysql')['database'];   
$_DI['config']->get('mysql')['username'];   
$_DI['config']->get('mysql')['this_is_pwd'];    
``` 

很不巧的是，密码首字符@并非希望读取配置文件，而是恰巧密码刚好首字母@，考虑  

到此情况，这里只是尝试读取，读取不到不会报错，仍然会把 `@this_is_pwd` 传给construct 

因此最终初始化参数如下: 

```php  
new \Catfan\Medoo([ 
    'database_type' => 'mysql', 
    'server'        => 'localhost', 
    'database_name' => 'test',  
    'username'      => 'root',  
    'password'      => '@this_is_pwd'   
]); 
``` 

动态注册    

如果希望每次从容器中获取服务时，都需要实例化一个新的对象，而且初始化参数可以动态改变    

那么可以采用动态注册， `dynamic=true`，示例如下 

``` php 
'logger' => [   
    'dynamic' => true,  
    'class'   => '\\Monolog\\Logger',   
    'params'  => [ '@channel' ],    
    'extend'  => function($obj, $di) {  
        $obj->pushHandler($di['logger_handler']);   
        return $obj;    
    },  
]   
``` 
这里指明了动态参数，`params` 会在每次实例化时重新读取配置文件   

`extend` 扩展函数用于修改从容器读取服务返回已实例化的对象， 

在扩展函数中若需要其他依赖服务，可以直接从 $di 中获取   

注册好后，就可以如下来声明不同频道的logger  

``` php 
$_DI['config']->set('logger', 'channel', 'manage'); 
$logger = $_DI['logger'];   //this is manage channel logger 
$_DI['config']->set('logger', 'channel', 'wxaccount');  
$logger = $_DI['logger'];   //this is wxaccount channel logger  
``` 



读取    

``` 
$redis = $_DI['redis']; 
$redis->set('name', 'khazix', 300); 
``` 

### 路由加载    

读取 routing 的配置列表，路由配置语法如下   

``` 
[method, routing, action]   
``` 

- method 支持以下几种   

    1. GET      
    2. POST     
    3. PUT      
    4. DELETE       

- routing 由两部分组成  

``` 
module/controller   
``` 

module 是模块名，表示业务所处的平台，一般如下:  

admin       => 后台管理系统(通常指PC)       
wxpage      => 微信页       
wxaccount   => 微信公众号       
wxgame      => 微信小游戏       
wxapp       => 微信小程序           
android     => 针对手机的API后台        
IOS         => 针对手机的API后台        


controller 是控制器名，关于控制器，后面会单独说明   

- action 行为   

routing 仅仅定位到哪个控制器， 然后根据`method`来定位具体的action   

与`Thinkphp` 的`pathinfo`直接定位到 action不同。具体说明参见控制器  


这里的设计思想参见阮一峰RESTful文章提出的建议进行尝试:  

即除定位目标名词使用`pathinfo` 其他辅助参数放到`queryString`    

示例    

``` php 
['GET',     '/manage/goods/(:num)',        'getGoodById'],  
['GET',     '/manage/goods',               'getGoodsList'], 
['PUT',     '/manage/goods/(:num)',        'updateGood'],   
['POST',    '/manage/goods',               'createGood'],   
['DELETE',  '/manage/goods/(:num)',        'deleteGood'],   
``` 

除 id 参数可以体现在路由上，其他需要传入都放在query string里    


### 控制器  

以上 bootstrap 程序引导起来后，通过 `bootstrap->run()` 就开始监听请求了 

然后会更加请求段传过来的 `pathinfo` 来定位控制器和方法。    

这里控制器设计还是比较粗糙，后面会根据实际使用来进行完善，控制器主要作用    

是接收 query string 或者其他 `php://input` 的数据来进行处理，控制器合理的   

设计是不直接进行业务处理，而是交给 manager， 管理器会根据 query string 来   

分配具体的业务执行器 business。此处补充 


### 模型    

尽可能将所有数据库操作通过模型完成，传入查询需要的条件和参数，返回结果集    

这里需要使用 implements 来高度抽象一些增删改查的传入值和返回值  


### 视图    

在此框架中没有视图，因为前后端完全分离，视图可以采用 nodejs 或者 原生js 结合`vue` `jquery`  

视图的代码如果是原生js，可以与后端框架代码放在一起(针对不想解决跨域方面的问题)，所放的位置有要求    

对于路由规则，会屏蔽URI含有 `/view/` `/test/` 的路由，因此可以放到类似如下位置  

`module/view/yourCode`  

尽管代码放在一起，但是视图代码和后台代码没有任何关联和影响，仅有的联系就是通过 API请求进行数据交互  


### 鉴权系统    

采用前后端分离的框架，鉴权系统上都比较难处理，传统MVC框架登录页可以直接跳转注册 session 或者 cookie 

前后端分离只能通过 ajax 调用API来进行鉴权登陆，而ajax限制了cookie 和session方面的传输，因此目前采用 

比较蠢的办法就是通过 GET 请求参数将数据 token 传给前端，前端解析url提取token 进行存储，这个方案很糟糕   

但是是临时性的，后面会引入 OAuth2.0 的鉴权系统。    

备注： 如果是 nodejs + vue 可以直接放到 http头部 Authorization 上，原生js无法读取 http头部  



### 返回状态码  

请求无效类的4xx:    

400 缺少参数    
404 找不到路由  
403 权限不够    


操作成功类的2xx:    
201 Created     
204 No Content  
200 Ok  


操作失败类的5xx:    
500: 脚本出错   
501: 不具备完成请求的功能   


有时候直接使用400异常返回前端，前端很难捕获异常消息 
所以用伪http状态码来模拟返回，本质还是200   

```php  
$this->reply(400, '缺少参数');  
$this->reply(403, '权限不够');  
$this->reply(501, '数据库执行失败');    
$this->reply(200, 'Ok', ['name'=>'test', 'age'=>'ok']); 
$this->reply(302, 'Moved Permanently', $url);   
``` 


## 类库文档 

`lib/khazix` 下均是自制的，其他`lib/`下均从composer里捞出来的，下面是一些可能会用到的文档   

- - -   
自制类文档 (//TODO) 

- [curl](https://github.com/demonxian3/php-simple-curl)       curl  
- [Request]         获取用户交互数据        
- [Utils]           通用工具        
- [Wxaccount]       微信公众号接口封装          
- [Xml]             微信xml封装         
- [Sms]             手机短信封装        

这里还对 monolog 进行了一些魔改，待会补充新增功能文档说明   

- - -   
第三方类文档    

- [monolog](https://github.com/Seldaek/monolog)       日志系统  

- [pimple](https://github.com/silexphp/Pimple)       依赖注入       

- [macaw](https://github.com/NoahBuscher/Macaw)       路由器    

- [medoo](https://medoo.in/)       ORM  

- [psr](https://github.com/php-fig)       PSR标准   

- [redis](http://github.com/phpredis/phpredis/)      Redis  


备注：这里对 monolog 进行了一些魔改，待会补充新增功能文档说明   


