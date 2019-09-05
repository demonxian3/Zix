<?php
namespace Module\Testing\Controller;

use Khazix\Request;
use Khazix\Log;
use Khazix\Http;

class Index
{
    public function __construct(){
        global $_DI;
        $this->di = $_DI;
        $this->wx = $_DI['wxaccount'];
    }

    public function test(){

        $xml = <<<EOF
<xml> <ToUserName><![CDATA[toUser]]></ToUserName> <FromUserName><![CDATA[fromUser]]></FromUserName> <CreateTime>12345678</CreateTime> <MsgType><![CDATA[news]]></MsgType> <ArticleCount>1</ArticleCount> <Articles> <item> <Title><![CDATA[title1]]></Title> <Description><![CDATA[description1]]></Description> <PicUrl><![CDATA[picurl]]></PicUrl> <Url><![CDATA[url]]></Url> </item> </Articles> </xml>
EOF;
        $json = <<<EOF
{ "data": [{ "TOP_LINE": 20000, "BOTTOM_LINE": 5000, "NOW_NUMBER": 3000, "THIS_MOUTH_TAKEIN": 0, "THIS_MOUTH_TAKEOUT": 0, "LAST_MOUTH_TAKEIN": 0, "LAST_MOUTH_TAKEOUT": 0, "RAW_MATERIAL_BATCH_NUMBER_": "SDS-7120", "OWN_ADRESS": "GEM", "MATERIAL_NAME_": "外星轮" }, { "TOP_LINE": 20000, "BOTTOM_LINE": 5000, "NOW_NUMBER": 3000, "THIS_MOUTH_TAKEIN": 0, "THIS_MOUTH_TAKEOUT": 0, "LAST_MOUTH_TAKEIN": 0, "LAST_MOUTH_TAKEOUT": 0, "RAW_MATERIAL_BATCH_NUMBER_": "SDS-7120", "OWN_ADRESS": "GEM", "MATERIAL_NAME_": "外星轮" } ], "success": true, "total": 2 }
EOF;

        $data = [
            'username' => 'kdsadas',
            'adsad' => 'dasdasds2132123',
        ];

        $logger = $this->di['logger'];

        $logger->setExtra('format', 'xml');

        $logger->print('subscriber', $xml);

        $logger->setExtra('format', 'json');

        $logger->print('json testing..', $data);
    }
}
