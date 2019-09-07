<?php
namespace Khazix;

/*
 * @params ($url,$data,$encode,$header,$ssl)
 * @method get(String $url)
 * @method get(String $url, Array $data)
 * @method post(String $url, Array $data, String $encode)
 * @method put(String $url, Array $data, String $encode)
 * @method delete(String $url)
 * @method delete(String $url, Array $data)
 * @method setHeader(Array $headers)
 * @method setOption(Array $options)
 * @method setSSL(Bool $isOpen)
 * @method setCert(String $ca, String $cert, String $key)
 */
class Curl
{

    public function __construct($timeout = 5, $certs=[])
    {
        $this->timeout = $timeout;
        $this->options = [];
        $this->headers = [];
        $this->certs = $certs;
        $this->customOpts = [];
        $this->customHdrs = [];

        $this->code = 0;
        $this->error = [];
        $this->result = [];
        $this->ssl = false;
    }

    public function __call($method, $arguments)
    {
    
        $this->headers = [];
        $this->options = [];

        $method = strtoupper($method);
        $url = $arguments[0];
        $data = $arguments[1] ?? '';
        $encode = $arguments[2] ?? '';
        $headers = $arguments[3] ?? [];
        $ssl = $arguments[4] ?? false;

        if ($data){
            if ($method == 'GET' || $method == 'DELETE'){
                $data  = http_build_query($data);
                $c = strpos($url, '?') ? '&' : '?';
                $url = $url.$c.$data;
            } else {
                if ($method == 'PUT') $encode='field';

                if ($encode){
                    $data = $this->$encode($data);
                }
                $this->options[CURLOPT_POST] = true;
                $this->options[CURLOPT_POSTFIELDS] = $data;
            }
        }

        $this->options[CURLOPT_URL] = $url;
        $this->options[CURLOPT_CUSTOMREQUEST] = $method;
        $this->options[CURLOPT_RETURNTRANSFER] = true;
        $this->options[CURLOPT_TIMEOUT] = $this->timeout;
        $this->options[CURLOPT_HTTPHEADER] = array_merge($this->headers, $headers, $this->customHdrs);


        if ($ssl){
            $this->options[CURLOPT_SSL_VERIFYPEER] = true;
            $this->options[CURLOPT_SSL_VERIFYHOST] = 0;
            $this->options[CURLOPT_CAINFO] = $this->certs['ca'];
            $this->options[CURLOPT_SSLCERT] = $this->certs['cert'];
            $this->options[CURLOPT_SSLCERTPASSWD] = $this->certs['key'];
        }

        if ($this->customOpts) $this->options = array_merge($this->customOpts, $this->options);
        $this->exec();
    }

    public function xml($data)
    {
        $this->headers[] = 'Content-Type:text/xml; charset=utf-8';

        $data = Utils::arrayToXml($data);
        return $data;
    }

    public function json($data)
    {
        $this->headers[] = 'Content-Type:application/json; charset=utf-8';
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $data;
    }

    public function form($data)
    {
        $this->headers[] = 'Content-Type:application/x-www-form-urlencoded; charset=utf-8';
        $data = http_build_query($data);
        return $data;
    }

    public function file($data)
    {
        $this->headers[] = 'Content-Type:multipart/form-data; charset=utf-8';

        $newData = [];
        foreach ($data as $key => $value){
            if (file_exists($value)) {
                $newData[$key] = new \CURLFile($value);
            } else{
                $newData[$key] = $value;
            }
        }
        return $newData;
    }

    public function field($data)
    {
        $data = (is_array($data)) ? http_build_query($data) : $data;
        $this->headers[] = "Content-Length: ". strlen($data);
        return $data;
    }

    public function exec() 
    {
        $con = curl_init();
        curl_setopt_array($con, $this->options);
        $res = curl_exec($con);

        $this->code = curl_getinfo($con, CURLINFO_HTTP_CODE);

        $this->error = false;
        if (curl_errno($con)){
            $this->error = [ 'code'=>curl_errno($con), 'msg'=>curl_error($con) ];
        }

        $this->result = json_decode($res,1) ?? $res;
        curl_close($con);
    }

    public function setCert($ca, $cert, $key) 
    {
        $this->certs['ca'] = $ca;
        $this->certs['key'] = $key;
        $this->certs['cert'] = $cert;
    }

    public function setOption(array $options)
    {
        $this->customOpts = $options;
    }

    public function setHeader(array $headers)
    {
        $this->customHdrs = $headers;
    }

}
