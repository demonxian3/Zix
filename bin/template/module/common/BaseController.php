<?php
namespace Module\Common;

use Common\ResponseTrait;

class BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        global $_DI;
        $this->_di = $_DI;
        $this->redis = $_DI['redis'];
        $this->request = $_DI['request'];
        $this->response = $_DI['response'];
        $this->config = $_DI['config'];
    }

}
