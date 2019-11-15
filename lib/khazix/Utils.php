<?php
namespace Khazix;

if (!function_exists('is_assoc')){
    function is_assoc($arr){
        if( !is_array($arr) ) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}

class Utils
{

    public function gbk2utf8($str){
        $charset = mb_detect_encoding($str, array('UTF-8', 'GBK', 'GB2312'));
        $charset = strtolower($charset);
        if ('cp936' == $charset) {
            $charset = 'GBK';
        }
        if ("utf-8" != $charset) {
            $str = iconv($charset, "UTF-8//IGNORE", $str);
        }
        return $str;
    }

    public function getCurrentUrl(): string
    {
        return $_SERVER['REQUEST_SCHEME'] .'://'. $_SERVER['HTTP_HOST'] .':'. $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
    }


    public function readCsvFile($filename, $titleMap, $filterList=[], $noEmptyList=[]){
        $result = [];
        $title = [];
        $isTitle = true;

        if (($handle = fopen($filename, "r")) === false) {
            return false;
        }

        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {

            if ($isTitle){
                $isTitle = false;

                //中文键转英文键
                foreach ($row as $col){
                    $col = self::gbk2utf8($col);
                    $title[] = $titleMap[$col];
                }
                continue;
            }

            $obj = [];
            $i = 0;

            $hasFilterList = count($filterList);
            $hasNoEmptyList = count($noEmptyList);

            foreach ($row as $col){
                $key = $title[$i++];
                $col = self::gbk2utf8($col);

                if ($hasNoEmptyList && in_array($key, $noEmptyList) && trim($col)=='' )
                    continue;

                if( $hasFilterList && in_array($key, $filterList) )
                    continue;    

                $obj[$key] = $col;
            }
            $result[] = $obj;
        }

        fclose($handle);
        return $result;
    }

    public static function arrayToXml($array, $root= null, $xml = null) { 
        $_xml = $xml; 
         
        if ($_xml === null) { 
            $_xml = new \SimpleXMLElement($root!== null ? $root: '<root/>'); 
        } 
         
        foreach ($array as $k => $v) { 
            if (is_array($v)) 
                self::arrayToXml($v, $k, $_xml->addChild($k)); 
            else 
                $_xml->addChild($k, $v); 
        } 

        return $_xml->asXML(); 
    } 

    public function xml_encode($arr)  
    {
        return '<xml>' . self::xml_build($arr) . '</xml>'.PHP_EOL;
    }


    public function xml_build($arr)
    {
        $str = "";
        foreach ($arr as $key => $value) {
            $inner = '';

            //字符串
            if (is_string($value)){
                $inner = "<![CDATA[{$value}]]>";
            }

            //关联数组
            else if (is_assoc($value)){
                $inner = self::xml_build($value);
            }

            //数字数组
            else if (is_array($value)){
                foreach ($value as $item) {
                    $inner .= "<item>" . self::xml_build($item) . "</item>".PHP_EOL;
                }
            }

            else{
                $inner = $value;
            }

            $str .= "<{$key}>{$inner}</{$key}>".PHP_EOL;
        } 

        return $str;
    }


    public static function xml_decode($str, $isArr=false){
        $obj = simplexml_load_string($str, "SimpleXMLElement", LIBXML_NOCDATA);
        $arr = json_decode(json_encode($obj), $isArr);
        return $arr;
    }
}
