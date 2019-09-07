<?php
namespace Khazix;

class JWT
{
    public function __construct()
    {
        $this->alg = 'SHA256';
        $this->iss = 'bds';
        $this->key = 'SeaWord!!';
        $this->expire = 7200;
    }
    public function b64encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '*_'), '=');
    }

    public function b64decode($data){
        return base64_decode(str_pad(strtr($data, '*_', '+/'), strlen($data)%4, '=', STR_PAD_RIGHT));
    }

    public function signature($data, $key, $alg)
    {
        return hash_hmac($alg, $data, $key);
    }

    public function jwtencode($payload)
    {
        $key = md5($this->key);
        $expire = $this->expire;

        $header = [
            'type' => 'JWT', 
            'alg'  => $this->alg 
        ];

        $payload['iss'] = $this->iss;
        $payload['iat'] = $_SERVER['REQUEST_TIME'];
        $payload['exp'] = $_SERVER['REQUEST_TIME'] + $expire;

        $jwt = [];
        $jwt[] = json_encode($header);
        $jwt[] = $this->b64encode(json_encode($payload));
        $jwt[] = $this->signature($jwt[1], $key, $this->alg);
        
        $token = implode('.', $jwt);
        return $token;
    }



    public function jwtdecode($token)
    {

        $key = md5($this->key);

        $jwt = explode('.', $token);
        
        $header = json_decode($jwt[0],1);
        
        $payload = json_decode($this->b64decode($jwt[1]),1);
        
        $alg = $header['alg'];
        
        $time = $_SERVER['REQUEST_TIME'];
        
        //校验请求是否过期
        if (isset($payload['iat'], $payload['exp']) && $time > $payload['iat'] && $time < $payload['exp']){

            //校验签名正确性
            if ( $jwt[2] === $this->signature($jwt[1], $key, $alg) ){
                return $payload;
            }
        }
        
        return NULL;
    }
}
