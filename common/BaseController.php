<?php

namespace Common;

class BaseController {

    public $_di;

    public function __construct()
    {
        global $_DI;
        $this->_di = $_DI;
    }

    public function reply($code, $msg, $data=[])
    {
        echo json_encode([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ]);exit;
    }

}
