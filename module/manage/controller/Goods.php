<?php
namespace Module\Manage\Controller;

use Khazix\Request;

class Goods
{
    public function showHeader(){
        echo '<pre>';
        foreach (getallheaders() as $name => $value) {
            echo "$name: $value\n";
        }

        $recvStr = file_get_contents("php://input");
        var_dump($recvStr);
    }

    public function searchGood(){
        print_r(Request::getQuery());
        echo 'get' ,BR;
        $this->showHeader();
    }

    public function createGood(){
        print_r(Request::getPost());
        print_r(Request::getFile());
        echo 'post' ,BR;
        $this->showHeader();
    }

    public function updateGood(){
        print_r(Request::getPut());
        echo 'put' ,BR;
        $this->showHeader();
    }

    public function deleteGood(){
        print_r(Request::getDelete());
        print_r(Request::getQuery());
        echo 'delete' ,BR;
        $this->showHeader();
    }
}
