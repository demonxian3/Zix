<?php

namespace Khazix\Sdk;

class Wxapp
{
    const OK = 0;
    const IllegalAesKey = -41001;
    const IllegalIv = -41002;
    const IllegalBuffer = -41003;
    const DecodeBase64Error = -41004;

    private $appid;
    private $appsecret;

    public function __construct()
    {
        global $_DI;
        $this->_di = $_DI;
        $this->curl = $this->_di['curl'];
        $this->config = $this->_di['config'];

        $this->appid = $this->config->get('wxapp')['appid'];
        $this->appsecret = $this->config->get('wxapp')['appsecret'];
    }

    public function authCode2Session($code)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session";

        $data = array(
            'appid' => $this->appid,
            'secret' => $this->appsecret,
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        );

        $this->curl->get($url, $data);
        return $this->curl->result;
    }

    public function authGetAccessToken()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token';

        $data = array(
            'grant_type' => 'client_credential',
            'secret' => $this->appsecret,
            'appid' => $this->appid,
        );

        $this->curl->get($url, $data);
        return $this->curl->result;
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData( $sessionKey, $encryptedData, $iv, &$data ):int
    {
        if (strlen($sessionKey) != 24) {
            return self::IllegalAesKey;
        }

        if (strlen($iv) != 24) {
            return self::IllegalIv;
        }

        $aesKey=base64_decode($sessionKey);
        $aesIV=base64_decode($iv);
        $aesCipher=base64_decode($encryptedData);
        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $result=json_decode( $result, true);

        if( $result  == NULL ) {
            return self::IllegalBuffer;
        }

        if( $result['watermark']['appid'] != $this->appid ){
            return self::IllegalBuffer;
        }

        $data = $result;
        return self::OK;
    }
}
