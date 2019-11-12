<?php
namespace Khazix\Sdk;

class TencentSms
{
    private $appid;

    private $appkey;

    private $tplid;

    public function __construct($appid, $appkey, $tplid)
    {
        $this->appid  = $appid;
        $this->appkey = $appkey;
        $this->tplid  = $tplid;
    }

    public function setContainer($di)
    {
        $this->_di = $di;
        $this->curl = $this->_di['curl'];
    }

    //根据模板1发送短信
    //{1}验证码是{2}，请在{3}分钟内填写。请勿将短信验证码提供给他人绑定
    public function send($phone, $code)
    {
        $timestamp = time();
        //$code = rand(101010, 989898);
        $url = "https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid={$this->appid}&random={$code}";
        $sig = hash("sha256","appkey={$this->appkey}&random={$code}&time={$timestamp}&mobile={$phone}");
        
        $data = [
            'ext' => '',
            'extend' => '',
            'params' => [' ', $code, 1],
            'sig' => $sig,
            'sign' => '师兄驾校',
            'tel' => [
                'mobile' => "$phone",
                'nationcode' => '86',
            ], 
            'time'=>$timestamp,
            'tpl_id' => $this->tplid,
        ];

        $this->curl->post($url, $data, 'json');

        if ($this->curl->result['errmsg'] === 'OK') return true;
        return false;
    }

}
