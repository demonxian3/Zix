<?php
namespace Wxaccount\Controller;

use Common\ResponseTrait;

class Sdk 
{
    use ResponseTrait;

    public function __construct()
    {
        global $_DI;

        $this->_di = $_DI;
        $this->config = $this->_di['config'];
        $this->config->set('logger_handler', 'filepath', DATA_DIR .DS. 'log' .DS. 'wxaccount_sdk.log');
        $this->logger = $this->_di['logger'];
        $this->request = $this->_di['request'];
        $this->response = $this->_di['response'];
        $this->wxaccount = $this->_di['wxaccount'];

    }

    public function main() 
    {
        $query = $this->request->getQuery();

        $token = $query['token'] ?? false;
        $task = $query['task'] ?? false;

        if ($token !== 'jntm') {
            return $this->reply(404);
        }

        if ($task === 'buildMenu') {
            $this->buildMenu();
        }

        return $this->reply(200);

    }

    public function broadcastMsg(){
        $openIds = ['oguWC5wYG3nwcLhPGP7LldD2n3J8', 'oguWC58jWOSp8O-YaF4DYXHSV8mg'];
        $openIds = ['oxDt41I7jsusKije0x_f3ykGeGUs', 'oxDt41NTtheMiLBa3Jq9nXCn4Huk'];
        $this->wxaccount->msendText($openIds, '这是群发测试，打扰啦');
    }

    public function sendTemplate(){
        $openid = 'oxDt41I7jsusKije0x_f3ykGeGUs';
        $template_id = 'fjcQu7Vy6clPPx5tFVtBSoE_CFm_xeJJKiiCmoYPO4U';
            $data = [
            'name' => [ 'value'=>'李子贤', 'color'=>'#173177' ],
            'item' => [ 'value'=>'练车预约', 'color'=>'#173177' ],
            'time' => [ 'value'=>'2019-08-16', 'color'=>'#173177' ],
            'addr' => [ 'value'=>'腾讯大厦', 'color'=>'#173177' ],
        ];
        $res = $this->wxaccount->sendTemplate($openid, $template_id, $data);
    }

    public function buildQrcode()
    {
        echo $this->wxaccount->buildQrcode('test', 300);
    }

    public function buildMenu()
    {
        $setting = $this->config->get('menu');
        $this->wxaccount->buildMenu($setting);
    }

    public function getMedia(){
        $data = Request::getQuery();
        var_dump($this->wxaccount->getTmpMedia($data['mediaId']));

    }

    public function uploadMedia(){
        $data = Request::getPost();
        $filename = Request::UPLOAD_PATH . $data['filename'];
        var_dump($this->wxaccount->uploadTmpMedia($filename, 'image'));
    }

    public function getMaterial(){

    }

    public function uploadMaterial(){

    }

    public function getAuthCode(){
        $url = $this->wxaccount->getAuthCodeUrl();
        header("Location: $url");exit;
    }

    public function getAuthToken(){
        $data = Request::getQuery();
        $code = $data['code'] ?? '';
        $state = $data['state'] ?? '';
        
        $acToken = $this->wxaccount->getTokenData($code);
        var_dump($acToken);
    }

    public function getAccessToken(){
        var_dump((new Wechat())->getAccessToken());
    }

    public function getUserInfo(){
        $openid = 'oxDt41I7jsusKije0x_f3ykGeGUs';
        var_dump((new Wechat())->getUserInfo($openid));
    }
}


