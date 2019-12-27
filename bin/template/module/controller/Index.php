<?php
namespace Module\Controller;

use Module\Common\BaseController;
use Model\Table as TableModel;

class Index extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function action1()
    {
        echo 'This is action1';
    }

    public function action2()
    {
        echo 'This is action2';
    }

}
