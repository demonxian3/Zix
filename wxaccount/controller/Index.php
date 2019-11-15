<?php
namespace Module\Wxaccount\Controller;

use Khazix\Request;
use Common\BaseController;

class Index extends BaseController
{
    public function __construct(){
        var_dump($this->_di);exit;
        $this->wx = $this->_di['wxaccount'];
        $this->logger = $this->_di['logger'];
    }

    //入口
    public function main(){
        try{
            $data = Request::getQuery();
            $echostr = $data['echostr'] ?? '';

            //接口配置校验
            if ($echostr) {

                $nonce = $data['nonce'];
                $signature = $data['signature'];
                $timestamp = $data['timestamp'];

                $res = $this->wx->check($nonce, $timestamp, $signature);
                if(!$res){
                    echo 'error';
                }
                echo $echostr;
                exit;
            }

            //监听客户端消息
            $this->logger->info('begin listen');
            $this->recvData = $this->wx->listen();
            $msgType = $this->recvData['MsgType'];
            $this->selectType($msgType);
            
        } catch (Exception $e) {
            $err = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
            Log::dump($err, 'error');
        }
    }

    //分类处理自动回复
    public function selectType($msgType){
        switch($msgType){
            case 'text':
                $this->textHandle();
            break;
            case 'voice':
                $this->voiceHandle();
            break;
            case 'image':
                $this->imageHandle();
            break;
            case 'video':
                $this->videoHandle();
            break;
            case 'shortvideo':
                $this->shortVideoHandle();
            break;
            case 'location':
                $this->locationHandle();
            break;
            case 'link':
                $this->linkHandle();
            break;
            case 'event':
                $this->eventHandle();
            break;
        }
    }

    //消息处理句柄
    public function textHandle(){
        $content = $this->recvData['Content'];
        $this->wx->sendText($content);
    }
    public function voiceHandle(){
        $mediaId = $this->recvData['MediaId'];
        $this->wx->sendVoice($mediaId);
    }
    public function imageHandle(){
        $mediaId = $this->recvData['MediaId'];
        $this->wx->sendImage($mediaId);
    }
    public function videoHandle(){
        $mediaId = $this->recvData['MediaId'];
        $this->wx->sendVideo($mediaId);
    }
    public function shortVideoHandle(){
        $mediaId = $this->recvData['MediaId'];
        $this->wx->sendVideo($mediaId);
    }
    public function locationHandle(){
        $recvData = $this->recvData;

        $x = $recvData['Location_X'];
        $y = $recvData['Location_Y'];
        $s = $recvData['Scale'];
        $l = $recvData['Label'];

        $str = "经度: $x". CR;
        $str .= "纬度: $y" .CR;
        $str .= "比例: $s" .CR;
        $str .= "信息: $l" .CR;

        $this->wx->sendText($str);
    }
    public function linkHandle(){
        $articles = [];
        $articles[] = [
            'Title' => '标题',
            'Description' => '描述',
            'PicUrl' => 'http://www.baidu.com',
            'Url' => 'http://www.baidu.com',
        ];
        $this->wx->sendNews($articles);
    }

    public function eventHandle(){
    }

    public function uploadMedia() {
        $tmpname = Request::getFileTmpName();
        $filename = Request::getFileName();
        $res = Request::saveFile($tmpname, $filename);
        var_dump($res);
    }
}
