<?php
namespace Module\Middleware;

use Common\ResponseTrait;

class Authorization 
{
    use ResponseTrait;

    public function __construct()
    {
        global $_DI;
        $this->expire = 7200;
        $this->redis = $_DI['redis'];
        $this->request = $_DI['request'];
        $this->response = $_DI['response'];
        $this->config = $_DI['config']->get('app');
    }

    public function login(): void
    {
    }

    public function check(): bool
    {
    }

    public function logout(): void
    {
    }
}

