<?php

namespace Khazix\Sdk;

/** 
 *
 * 文档地址: https://help.aliyun.com/document_detail/101343.html?spm=a2c4g.11186623.6.610.2ea07535thsKv7 
 *
 * 仅用于短信验证码
 *
 */

class Sms
{
    /** 访问密钥 ID。AccessKey 用于调用 API。*/
    private $accessKeyId;

    /** 访问密钥 */
    private $accessSecret;

    /** API 的名称*/
    private $action;

    /** 签名方式。取值范围：HMAC-SHA1*/
    private $signatureMethod = 'HMAC-SHA1';

    /** 签名唯一随机数。用于防止网络重放攻击，建议每次请求都用随机数*/
    private $signatureNonce;

    /** 签名算法版本。取值范围：1.0*/
    private $signatureVersion = '1.0';

    /** 请求的时间戳。按照ISO8601 标准表示，并需要使用UTC时间，格式为yyyy-MM-ddTHH:mm:ssZ*/
    private $timestamp;

    /** API 的版本号,取值范围：2017-05-25*/
    private $version = '2017-05-25';

    /** 公司签名 */
    private $signName;

    /** 短信模板 */
    private $templateCode;

    /** 短信内容 */
    private $templateParam;

    /** 电话号码 */
    private $phoneNumbers;

    /** 短信服务统一使用以下公网服务地址 */
    private $endPoint;

    /** 返回格式 */
    private $format = 'JSON';

    /** 存放所有请求参数 */
    private $requestParams = array();

    /** 存放所有请求参数 */
    private $queryString = '';

    public function __construct()
    {
        global $_DI;

        $this->curl = $_DI['curl'];
        $this->logger = $_DI['logger'];
        $this->config = $_DI['config']->get('sms_aliyun');

        $this->logger->setChannel('Alicloud Sms');
        $this->endPoint = 'https://dysmsapi.aliyuncs.com';
        $this->setCommonRequestHeaders();

    }

    /** 设置公共请求参数 */
    private function setCommonRequestHeaders()
    {
        $this->signName = $this->config['signName'];
        $this->accessKeyId = $this->config['accessKeyId'];
        $this->accessSecret = $this->config['accessSecret'];
        $this->templateCode = $this->config['templateCode'];
        $this->signatureNonce = uniqid();
        $this->timestamp = gmdate('Y-m-d\TH:i:s\Z');
    }

    private function buildRequestArray(): void
    {
        $this->requestParams['Action']           = $this->action;
        $this->requestParams['AccessKeyId']      = $this->accessKeyId;
        $this->requestParams['Format']           = $this->format;
        $this->requestParams['PhoneNumbers']     = $this->phoneNumbers;
        $this->requestParams['SignName']         = $this->signName;
        $this->requestParams['SignatureMethod']  = $this->signatureMethod;
        $this->requestParams['SignatureNonce']   = $this->signatureNonce;
        $this->requestParams['SignatureVersion'] = $this->signatureVersion;
        $this->requestParams['Timestamp']        = $this->timestamp;
        $this->requestParams['TemplateParam']    = $this->templateParam;
        $this->requestParams['TemplateCode']     = $this->templateCode;
        $this->requestParams['Version']          = $this->version;
    }

    public function signature(string $method): void
    {
        ksort($this->requestParams);

        $canonicalized = '';
        foreach ($this->requestParams as $key => $value) {
            $canonicalized .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }

        $stringToSign = $method . '&%2F&' . $this->percentEncode(substr($canonicalized, 1));
        $sign = base64_encode(hash_hmac("sha1", $stringToSign, $this->accessSecret . "&", true));
        $signature = $this->percentEncode($sign);
        $this->requestParams['Signature'] = $signature;
        $this->queryString = "Signature={$signature}{$canonicalized}";

    }


    /** 下面两个方法是阿里云所谓的: POP签名拼接规则 */
    private function percentEncode(string $string): string
    {
        $res = urlencode($string);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
    }

    public function sendSms($phone, $code)
    {
        $this->action = 'SendSms';
        $this->phoneNumbers = $phone;
        $this->templateParam = json_encode(['code' => $code]);

        $this->buildRequestArray();
        $this->signature('POST');


        $this->curl->post($this->endPoint, $this->queryString);
        $this->logger->print($this->queryString, $this->curl->result);
    }


}
