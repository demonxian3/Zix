<?php
namespace Wxaccount\Controller;

use Common\ResponseTrait;

class Index 
{

    use ResponseTrait;

    public function __construct(){
        global $_DI;
        $this->_di = $_DI;
        $config = $this->_di['config'];
        $config->set('logger_handler', 'filepath', DATA_DIR .DS. 'log' .DS. 'wxaccount_index.log');
        $this->wxaccount = $this->_di['wxaccount'];
        $this->logger = $this->_di['logger'];
        $this->request = $this->_di['request'];
    }

    //入口
    public function main(){
        try{
            $data = $this->request->getQuery();
            $echostr = $data['echostr'] ?? '';

            //接口配置校验
            if ($echostr) {

                $nonce = $data['nonce'];
                $signature = $data['signature'];
                $timestamp = $data['timestamp'];

                $res = $this->wxaccount->check($nonce, $timestamp, $signature);
                if(!$res){
                    echo 'error';
                }
                echo $echostr;
                exit;
            }

            //监听客户端消息
            $this->logger->info('begin listen');
            $this->recvData = $this->wxaccount->listen();
            $msgType = $this->recvData['MsgType'];
            $this->selectType($msgType);
            
        } catch (Exception $e) {
            $err = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
            $this->logger->error($err);
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
        $this->wxaccount->sendText($content);
    }
    public function voiceHandle(){
        $mediaId = $this->recvData['MediaId'];
        $this->wxaccount->sendVoice($mediaId);
    }
    public function imageHandle(){
        $mediaId = $this->recvData['MediaId'];
        $this->wxaccount->sendImage($mediaId);
    }
    public function videoHandle(){
        $mediaId = $this->recvData['MediaId'];
        $this->wxaccount->sendVideo($mediaId);
    }
    public function shortVideoHandle(){
        $mediaId = $this->recvData['MediaId'];
        $this->wxaccount->sendVideo($mediaId);
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

        $this->wxaccount->sendText($str);
    }
    public function linkHandle(){
        $articles = [];
        $articles[] = [
            'Title' => '标题',
            'Description' => '描述',
            'PicUrl' => 'http://www.baidu.com',
            'Url' => 'http://www.baidu.com',
        ];
        $this->wxaccount->sendNews($articles);
    }

    public function eventHandle(){
        $openId = $this->recvData['FromUserName'];
        $event = strtolower($this->recvData['Event']);
        $eventKey = $this->recvData['EventKey'];

    
        switch ($event) {
            case 'subscribe':
                $this->wxaccount->sendText("没什么好说，但总的说点什么，不然对于从忙忙人海中寻到这的您来说，显得不那么礼貌。\n\n这归属深信师兄团队运营，来了皆是朋友，校内大小忙您尽管开口。我们将竭尽所能。\n\n【PS】有想法的小伙伴可加入师兄团队"); break;

            case 'click':
                switch ($eventKey) {
                    case 'info':
                        $this->logger->info('openId', $openId);
                        $userInfo = $this->wxaccount->getUserInfo($openId);
                        $str  = '昵称: '. $userInfo['nickname'] .CR;
                        $str .= '性别: '. ($userInfo['sex'] == 1 ? '男' : '女') .CR;
                        $str .= '住址: '. $userInfo['province']. $userInfo['city'] .CR ;
                        $str .= '头像: '. $userInfo['headimgurl'] .CR ;
                        $str .= '备注: '. $userInfo['remark'] .CR ;
                        $str .= '订阅方式: '. $userInfo['subscribe_scene'] .CR ;

                        $this->wxaccount->sendText($str);
                        break;

                    case 'news':
                        $url = $wx->getAuthCodeUrl();
                        $this->wxaccount->sendText('开发中...');
                        break;
                }

                break;

            default:
                # code...
                break;
        }
    }
}
