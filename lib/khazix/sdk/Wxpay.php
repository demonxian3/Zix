<?php
namespace Khazix\Sdk;

use Khazix\Utils;

//https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=7_7&index=5
class Wxpay
{
    private $key;
    private $appid;
    private $appsecret;
    private $mchid;
    private $notifyUrl;

    private $params = [];

    public function __construct()
    {
        global $_DI;

        $this->curl = $_DI['curl'];
        $this->redis = $_DI['redis'];
        $this->config = $_DI['config'];
        $this->logger = $_DI['logger'];
        $this->logger->setChannel('wxpay sdk');
        $this->logger->setExtra('format', 'json');

        $wxappConf = $this->config->get('wxapp');
        $wxpayConf = $this->config->get('wxpay');

        $this->key = $wxpayConf['key'];
        $this->mchid = $wxpayConf['mchid'];
        $this->appid = $wxappConf['appid'];
        $this->appsecret = $wxappConf['appsecret'];
        $this->notifyUrl = $wxpayConf['notify_url'];

    }

    //统一下单
    public function unifiedOrder(string $openid, string $orderSn, string $body, string $total_fee)
    {
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

        //回调签名，类似短信验证码验证
        $code = md5(time());

        //换算成分
        $total_fee = intval($total_fee * 100);

        $this->params['attach'] = $code;
        $this->params['body'] = $body;
        $this->params['total_fee'] = $total_fee;
        $this->params['openid'] = $openid;
        $this->params['mch_id'] = $this->mchid;
        $this->params['appid'] = $this->appid;
        //$this->params['appsecret'] = $this->appsecret;
        $this->params['notify_url'] = $this->notifyUrl;
        $this->params['timeStamp'] = time();
        $this->params['nonce_str'] =  $this->createNonce();
        $this->params['out_trade_no'] = $orderSn;
        $this->params['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
        $this->params['trade_type'] = 'JSAPI';
        $this->params['sign'] = $this->sign($this->params, $this->key);

        $this->logger->print('pay params', $this->params);

        $xml = $this->xml_encode();
        $this->curl->setHeader(['Content-Type:text/xml; charset=utf-8']);
        $this->curl->post($url, $xml);

        $data = Utils::xml_decode($this->curl->result, true);

        if ($data) {
            $this->redis->set('pay_code_'.$orderSn, $code);
        }

        return $this->paySign($data);
    }

    //再次签名
    public function paySign(array $data): array
    {
        $reply = array(
            'appId' => $data['appid'],
            'timeStamp' => (string)time(),
            'nonceStr' => $this->createNonce(),
            'signType' => 'MD5',
        );

        if (isset($data['prepay_id'])) {
            $reply['package'] = 'prepay_id='.$data['prepay_id'];
        } else {
            $reply['errMsg'] = $data['err_code_des'];
        }

        $reply['paySign'] = $this->sign($reply, $this->key);
        return $reply;
    }

    private function sendHttpXml($url, $xml){
        $con = curl_init((string)$url);
        if(!stristr($xml,"<xml>")) 
            return "xml invalid";
        curl_setopt($con, CURLOPT_POST, true);
        curl_setopt($con, CURLOPT_HTTPHEADER, Array("Content-Type:text/xml; charset=utf-8"));
        curl_setopt($con, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($con, CURLOPT_TIMEOUT, 5);
        curl_setopt($con, CURLOPT_VERBOSE, 1);
        $result = curl_exec($con);
        curl_close($con);
        return $result;
    }

    //xml编码
    private function xml_encode(): string
    {
        $data = $this->params;
        $xml = "<xml>";
        foreach ($data as $key => $val) {
            if ($key != 'sign') {
                $xml .= "<$key><![CDATA[$val]]></$key>";
            } else {
                $xml .= "<$key>$val</$key>";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    //生成随机字符串
    private function createNonce($length = 32): string
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    //生成订单号
    private function createTradeNo(): string
    {
        return date("YmdHis").mt_rand(14332423, 98929678);
    }

    //MD5签名: key是支付密钥
    private function sign(array $params, string $key): string
    {
        ksort($params);
        $string = http_build_query($params);
        $string = urldecode($string);
        #$string = $this->kvConcat($params);
        #echo $string;exit;
        #var_dump($string);exit;
        $string = $string . "&key=" . $key;
        $string = md5($string);
        $result = strtoupper($string);
        return $result;
    }

    private function kvConcat($urlObj){
        $buff = "";
        foreach ($urlObj as $k => $v) {
            if ($k != "sign") {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
}
