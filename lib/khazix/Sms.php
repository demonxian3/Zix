<?php
namespace Khazix;

use common\Config;

class Sms
{
    public function __construct()
    {
        $this->appid  = Config::sms_appid;
        $this->tplid  = Config::sms_tplid;
        $this->appkey = Config::sms_appsecret;
    }

    //根据模板1发送短信
    //{1}验证码是{2}，请在{3}分钟内填写。请勿将短信验证码提供给他人绑定
    public function sendSMS_tpl1($phone, $code)
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

        $res = Http::postJson($url, $data);
        if ($res['errmsg'] === 'OK') return true;
        else return false;

    }

}
