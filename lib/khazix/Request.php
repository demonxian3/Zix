<?php
namespace Khazix;

class Request
{
    const UPLOAD_PATH = DATA_DIR . DS . 'upload/';

    public static $fileData = [];

    public function getQuery()
    {
        if (count($_GET)) 
            return $_GET;
        return NULL;
    }

    public function getPost()
    {
        if (count($_POST)) 
            return $_POST;
        return NULL;
    }

    public function getPut()
    {
        $GLOBALS['_PUT'] = [];
        if ($_SERVER['REQUEST_METHOD'] == 'PUT'){
            $data = file_get_contents('php://input');
            parse_str($data, $GLOBALS['_PUT']);
        } 

        return $GLOBALS['_PUT'];
    }

    public function getCookie()
    {
        return $_COOKIE;
    }

    public function getDelete()
    {
        $GLOBALS['_DELETE'] = [];
        if ($_SERVER['REQUEST_METHOD'] == 'DELETE'){
            $data = file_get_contents('php://input');
            parse_str($data, $GLOBALS['_DELETE']);
        } 

        return $GLOBALS['_DELETE'];
    }

    public function getFiles(){

        $fileList = [];

        if (count($_FILES)){
            foreach ($_FILES as $file){
                array_push($fileList , $file);              
            }
        }

        return $fileList;
    }

    public function getFile(){
        if (count($_FILES)){
            self::$fileData = array_shift($_FILES);
            return self::$fileData;
        }
        return NULL;
    }

    public function getFileName(){
        if (self::$fileData){
            return self::$fileData['name'];
        }
        return self::getFile()['name'];
    }

    public function getFileTmpName(){
        if (self::$fileData){
            return self::$fileData['tmp_name'];
        }
        return self::getFile()['tmp_name'];
    }

    public function saveFile($tmpname, $filename){
        $savepath = self::UPLOAD_PATH . $filename;

        if (!file_exists($savepath)) 
            move_uploaded_file($tmpname, $savepath);

        return $savepath;
    }

}
