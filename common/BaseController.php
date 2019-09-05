<?php

namespace common;

use lib\Request;

class BaseController {

    public function reply($code, $msg, $data=[]){
        echo json_encode([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ]);exit;
    }

    public function connectRedis()
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        return $redis;
    }

}
