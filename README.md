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
│   ├── BaseController.php                      基础控制器
│   └── MysqlBaseModel.php                      基础模型
├── config                                  配置
│   ├── development                             开发环境配置目录
│   │   └── setting.php                         
│   ├── routing.php                             路由表
│   ├── service.php                             服务注册表
│   └── setting.php                             配置表
├── core                                    核心
│   ├── Bootstrap.php                           引导文件
│   ├── ConfigLoader.php                        配置加载器
│   └── Provider.php                            依赖加载器
├── data                                    数据
│   ├── log                                     日志
│   └── upload                              
├── lib                                     类库
│   ├── catfan                                  ORM
│   ├── khazix                                  自制类
│   │   ├── Curl.php                                Curl请求封装
│   │   ├── JWT.php                                 JWT鉴权封装
│   │   ├── Request.php                             用户交互数据封装
│   │   ├── Sms.php                                 短信API
│   │   ├── Utils.php                               工具类
│   │   └── Wxaccount.php                           微信公众号API
│   ├── monolog                                 日志
│   ├── noahbuscher                             路由
│   ├── pimple                                  依赖注入
│   └── psr                                     PSR标准
│       ├── container
│       └── log
├── module                                  模块
│   └── manage                                  模块名
│       ├── controller                              控制器
│       ├── model                                   模型
│       └── view                                    视图
├── public                                  开放
│   └── index.php                               入口文件
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

文件名(filename), 空间名(namespace), 类名(class) 一致性参考 PSR-4 ，首字母均大写

目录名，和普通php文件均全小写，其他没有要求


- 编码规范

数据库，前后端，缓存等统一为 `UTF-8` 编码，时区设置为标准时间 `UTC`

### 引导流程

入口文件定义一些路径常量，然后引入 bootstrap 程序来引导框架，分以下步骤:

1. 初始化自动加载器   
2. 初始化配置加载器    
3. 初始化依赖加载器    
4. 初始化路由加载器    

这些过程可在 `Core\Bootstrap->__construct()` 看到


### 自动加载

- 加载规则

当使用`use`引入对象时，根据`PSR-4`一致性原则，通过`namespace`寻找类对应的文件路径并尝试引入，

在引入失败情况下，会到 `lib/` 目录下进行尝试引入，再失败的情况则抛出异常，因此项目并非完全遵循 PSR-4 标准。

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

如果需要读取配置文件，可以在前面加上 @ 符号，`__function_parse_params` 会尝试读取配置文件

服务名为 `mysql`, 初始化参数为 `['@username', '@notice_this_is_password']`, 那么解析后会变成如下

`['root', '@notice_this_is_password']`

- extend

扩展匿名函数，对已经初始化后的服务进行扩展，如 redis 的初始连接，具体参考 [pimple文档] (https://packagist.org/packages/pimple/pimple)

``` php
'redis' => [
    'extend' => function($obj, $di) {
        $config = $di['config'];
        $obj->connect($config['host'], $config['port']);
        return $obj;
    };
]
```


示例

参阅代码 `config/service.php`


### 类库

所有类库文件均放在 `/lib` 下，没有使用 composer 加载，加载规则参考下一节：自动加载

类库路径 `lib/`

#### 自制类库: `lib/khazix`

- Curl          构造http请求包
- Request       获取用户交互数据
- Utils         通用工具
- Wxaccount     微信公众号接口封装
- Xml           微信xml封装
- Sms           手机短信封装

#### 第三方类库: 

`lib/` 下除自制类库，其他均为第三方类库，均来源与composer，下面是他们所对应的文档

-[monolog](https://github.com/Seldaek/monolog)     日志系统

-[pimple](https://github.com/silexphp/Pimple)      依赖注入    

-[macaw](https://github.com/NoahBuscher/Macaw)       路由器

-[medoo](https://medoo.in/)       ORM

-[psr](https://github.com/php-fig)         PSR标准


这里对 monolog 进行了一些魔改，待会补充新增功能文档说明



### 路由

这里是对阮一峰提出的提议进行尝试: 除目标名词使用`pathinfo` 其他辅助参数放到`queryString`

优点:

- 避免路由泛滥
- 减少路由命名思考浪费的时间
- 解决路由冲突

路由格式:

```
/module/controller
```

`module` 是模块名，用于描述业务逻辑所处的平台，如下

admin       => 后台管理系统(通常指PC)
wxpage      => 微信页
wxaccount   => 微信公众号
wxgame      => 微信小游戏
wxapp       => 微信小程序
android     => 针对手机的API后台
IOS         => 针对手机的API后台

`controller` 是控制器名，用于处理业务逻辑，命名上有几点建议:

1. 尽可能使用名词而不是动词: appointment 要好于 appoint
2. 尽可能使用复数: users 要好于 user
3. 尽可能`控制器名`和`表名`和`模型名`一致

示例:

```
GET    /manage/appointments?type="invite"
POST   /manage/appointments?type="appoit"
PUT    /manage/appointments?mode="coach"
DELETE /manage/appointments?mode="trainee"
```

路由仅将流程定位到控制器，而具体的行为根据 `queryString` 来做选择，因此每个控制器

都要有对 GET POST PUT DELETE 这个四个方法的接收入口，入口会处理 `queryString` 从而

分发到不同的 `action method` 上面


## 模型

上面提到控制器名 与 表名一致，操作控制器相当于操作表，如下

```
GET     admin/users  =>  SELECT * FROM users; 
PUT     admin/users  =>  UPDATE users SET {some $_PUT};
DELETE  admin/users  =>  DELETE FROM users {some $_WHERE};
```


## 视图

在此框架中没有视图，因为前后端完全分离，视图可以采用 nodejs 或者 原生js 结合`vue` `jquery`

视图的代码如果是原生js，可以与后端框架代码放在一起(针对不想解决跨域方面的问题)，所放的位置有要求

对于路由规则，会屏蔽URI含有 `/view/` `/test/` 的路由，因此可以放到类似如下位置

`module/view/yourCode`

尽管代码放在一起，但是视图代码和后台代码没有任何关联和影响，仅有的联系就是通过 API请求进行数据交互


## 鉴权系统

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
