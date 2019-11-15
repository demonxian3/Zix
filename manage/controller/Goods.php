<?php
namespace Manage\Controller;

use Khazix\Http\Url;
use Khazix\Http\Response;
use Khazix\Http\Request;
use Khazix\Utils;

class Goods
{
    public function __construct()
    {
        echo '<pre>';
    }


    public function entrance()
    {
        $rep = new Request();
        $url = $rep->getUrl();
        $url = new Url($url);
        var_dump($rep->detectLanguage(['zh', 'zh-cN', 'jp']));
    }
}

