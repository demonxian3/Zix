<?php

namespace Khazix;

class Wxaccount
{

    public $sendData = [];

    public function __construct($token, $appid, $secret, $di=null)
    {

        $this->token = $token;
        $this->appid = $appid;
        $this->appsecret = $secret;

        if ($di) {
            $this->setContainer($di);
        }
            
    }

    //设置依赖注入
    public function setContainer($di){
        $this->_di = $di;

        $this->config = $di['config'];
        $this->curl = $di['curl'];
        $this->redis = $di['redis'];
        
        $this->config->set('logger', 'channel', 'wxaccount');
        $this->logger = $di['logger'];
        $this->logger->setExtra('format', 'json');
    }

    //消息接口校验
    public function check($nonce, $timestamp, $signature){
        $token = $this->token;

        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr === $signature)
            return true;
        else
            return false;
    }

    //监听获取微信服务器发送过来的消息
    public function listen()
    {
        $recvStr = file_get_contents("php://input");
        if(!$recvStr){
            $this->logger->notice('No post data');
            return false;
        }

        $this->logger->printXml('receive post data', $recvStr);
        $recvData = Utils::xml_decode($recvStr);

        $this->$sendData['ToUserName'] = $recvData['FromUserName'];
        $this->$sendData['FromUserName'] = $recvData['ToUserName'];
        $this->$sendData['CreateTime'] = time();

        return $recvData;
    }

    /************************************/
    /*****         自动回复           ****/
    /************************************/
    //回复消息给客户端
    public function reply($mediaData){
        $sendData = $this->$sendData;
        $sendData = array_merge($sendData, $mediaData);
        $sendXml = Utils::xml_decode($sendData, true);
        $this->logger->printXml('send data', $sendXml);
        echo $sendXml;
        exit;     
    }

    public function sendText($content) {
        $this->reply(array(
            'MsgType' => 'text',
            'Content' => $content,
        ));
    }

    public function sendImage($mediaId){
        $this->reply(array(
            'MsgType' => 'image',
            'Image' => [
                'MediaId' => $mediaId,
            ],
        ));
    }

    public function sendVoice($mediaId){
        $this->reply(array(
            'MsgType' => 'voice',
            'Voice' => [
                'MediaId' => $mediaId,
            ],
        ));
    }

    public function sendVideo($mediaId, $title="", $description=""){
        $this->reply(array(
            'MsgType' => 'video',
            'Video' => [
                'MediaId' => $mediaId,
                'Title' => $title,
                'Description' => $description,
            ],
        ));
    }

    public function sendMusic($mediaId, $title="", $description="", $musicUrl="", $HQMusicUrl=""){
        $this->reply(array(
            'MsgType' => 'music',
            'Music' => [
                'ThumbMediaId' => $mediaId,
                'Title' => $title,
                'Description' => $description,
                'HQMusicUrl' => $HQMusicUrl,
                'MusicURL' => $musicUrl,
            ],
        ));
    }

    public function sendNews($articles){
        $this->reply(array(
            'MsgType' => 'news',
            'ArticleCount' => count($articles),
            'Articles' => $articles,
        ));
    }

    /************************************/
    /*****         群发消息           ****/
    /************************************/
    public function mreply($data)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token={$this->getAccessToken()}";
        $this->curl->post($url, $data, 'json');
        exit;
    }

    public function msendText($openIds, $content)
    {
        $this->mreply(array(
            'touser' => $openIds,
            'msgtype' => 'text',
            'text' => [
                'content' => $content,
            ]
        ));
    }

    public function msendVoice($openIds, $mediaId)
    {
        $this->mreply(array(
            'touser' => $openIds,
            'msgtype' => 'voice',
            'voice' => [
                'media_id' => $mediaId,
            ]
        ));
    }

    public function msendImage($openIds, $mediaId)
    {
        $this->mreply(array(
            'touser' => $openIds,
            'msgtype' => 'image',
            'image' => [
                'media_id' => $mediaId,
            ]
        ));
    }

    public function msendNews($openIds, $mediaId)
    {
        $this->mreply(array(
            'touser' => $openIds,
            'msgtype' => 'mpnews',
            'mpnews' => [
                'media_id' => $mediaId,
            ],
            'send_ignore_reprint' => 0
        ));
    }

    /************************************/
    /*****         模板消息           ****/
    /************************************/
    public function sendTemplate($openid, $template_id, $detail, $data){
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$this->getAccessToken()}";
        
        $data = array(
            'touser' => $openid,
            'template_id' => $template_id,
            'url' => $detail,
            'data'=> $data,
        );

        $this->curl->post($url, $data, 'json');
        return $this->curl->result;
    }

    /************************************/
    /*****         素材管理           ****/
    /************************************/
    public function uploadTmpMedia($filename, $type)
    {
        $media_id = $this->redis->get("media_{$type}_{$filename}");
        if ($media_id) return $media_id;

        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$this->getAccessToken()}&type={$type}";

        $this->curl->post($url, ['file'=>$filename], 'file');

        if ($this->curl->result) {
            $this->redis->set("media_{$type}_{$filename}", $res['media_id'], 259200);
            return $res['media_id'];
        }

        return NULL;
    }

    public function getTmpMedia($mediaId)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token={$this->getAccessToken()}&media_id={$mediaId}";
        $this->curl->get($url);
        return $this->result;
    }

    /************************************/
    /*****         微页接口           ****/
    /************************************/
    public function getAuthCodeUrl($url='', $state='')
    {
        $appid = $this->appid;
        $appsecret = $this->appsecret;
        $redirectUri = $url;
        $responseType = 'code';
        $scope = 'snsapi_userinfo';
        $state = urlencode($state);
        $redirectUri = urlencode($redirectUri);

        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$redirectUri}&response_type={$responseType}&scope={$scope}&state={$state}#wechat_redirect";

        return $url;
    }

    public function getTokenData($code){

        $appid = $this->appid;
        $appsecret = $this->appsecret;
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$appsecret}&code={$code}&grant_type=authorization_code";

        $this->curl->get($url);
        return $this->curl->result;
    }

    public function getUserData($openid, $token){
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$token}&openid={$openid}&lang=zh_CN";
        $this->curl->get($url);
        return $this->curl->result;
    }

    /************************************/
    /*****         其他接口          ****/
    /************************************/
    public function getAccessToken()
    {
        $accessToken = $this->redis->get('access_token');

        if (!$accessToken){
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->appsecret}";

            $this->logger->info('send api: get token', $url);
            $this->curl->get();
            $data = $this->curl->result;

            $this->redis->set('access_token', $data['access_token'], 7200);
            $accessToken = $data['access_token'];
        }

        return $accessToken;
    }

    //获取用户信息
    public function getUserInfo($openId)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$this->getAccessToken()}&openid={$openId}&lang=zh_CN";

        $this->curl->get($url);
        return $this->curl->result;
    }

    //生成二维码
    public function buildQrcode($scene, $expire)
    {
        $url = $this->redis->get("qrcode_{$scene}_url");
        if ($url) return $url;

        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$this->getAccessToken()}";
        $actionName = $this->getQrSceneType($scene, $expire);

        $data = [
            'expire_seconds' => $expire,
            'action_name' => $actionName,
            'action_info' => [
                'scene' => [
                    'scene_id' => $scene,
                    'scene_str' => $scene,
                ]
            ]
        ];

        $this->curl->post($url, $data, 'json');

        $res= $this->curl->result;

        if (isset($res['url'])){
            $this->redis->set("qrcode_{$scene}_url", $res['url'], $expire);
        }
        return $res['url'];
    }

    //根据时间和类型判断参数类型
    protected function getQrSceneType($scene, $expire)
    {
        if (is_string($scene)){
            if ($expire == 0){
                return 'QR_LIMIT_STR_SCENE';
            }
            return 'QR_STR_SCENE';

        }else{
            if ($expire == 0){
                return 'QR_LIMIT_SCENE';
            }

            return 'QR_SCENE';
        }
    }

    //生成菜单
    public function buildMenu($setting)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$this->getAccessToken()}";

        $this->logger->info('send api: build menu', $url);

        $this->curl->post($url, $setting, 'json');

        return $this->curl->result;
    }


}
