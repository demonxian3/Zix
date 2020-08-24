<?php

namespace Common; 

trait ResponseTrait
{
    static $ERRMAP = [
        40101 => 'LoginFail',
        40001 => 'MissingRequestParams',
        'LoginFail'             =>  40101,
        'MissingRequestParams'  =>  40001,
    ];

    public function __construct()
    {
        global $_DI;
        $this->response = $_DI['response'];
    }

    public function reply(int $code, string $msg = '', $data = []): void
    {
        if (!$this->response->isSent()) {
        $this->response
            ->setStatus($code)
            ->setContentType('Application/json', 'utf-8')
            ->sendHeaders();
        echo json_encode(array('msg' => $msg, 'data' => $data));
        exit();
                                        
        }
        throw new \Exception('Possible problem: you are sending a HTTP header while already
            having some data in output buffer. Try start session earlier.');
    }

    public function replySuccess($data = [], string $msg = 'ok'): void
    {
        $this->reply(200, 'ok', $data);
    }

    public function replyError(string $key, string $msg = 'error'): void
    {
        if (is_numeric($key)) {
            $data = [
                'ret' => $key,
                'msg' => self::$ERRMAP[$key],
            ];
        } else {
            $data = [
                'ret' => self::$ERRMAP[$key],
                'msg' => $key,
            ];
        }

        $this->reply(400, $msg, $data);
    }

    public function redirect(string $url): void
    {
        if (!$this->response->isSent()) {
            $this->response
            ->redirect($url);
            exit();
        }

        throw new \Exception('Possible problem: you are sending a HTTP header while already
            having some data in output buffer. Try start session earlier.');
    }

    public function setCookie(string $name, string $value, int $time): void
    {
        $this->response->setCookie($name, $value, $time);
    }

    public function deleteCookie(string $name): void
    {
        $this->response->deleteCookie($name);
    }
}
