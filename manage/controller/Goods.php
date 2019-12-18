<?php
namespace Manage\Controller;

use Khazix\Http\Url;
use Khazix\Http\Response;
use Khazix\Http\Request;
use Khazix\Utils;

use Model\Trainee;
use Model\Coach;
use Model\Binding;
use Model\Appointment;

class Goods
{
    public function __construct()
    {
        echo '<pre>';
    }
    
    public function entrance()
    {
    	$coach = new Coach();
    	$trainee = new Trainee();
    	$binding = new Binding();
    	$appointment = new Appointment();

    	$data = $binding
    		->innerJoin($trainee, ['tid'=>'id'], ['id'=>'trainee_id'])
    		->innerJoin($coach, ['cid'=>'id'], ['id' => 'coach_id', 'truename'=>'coach_truename'])
    		->leftJoin($appointment, ['trainee.openid'=>'openid'])
    		->show([]);
    }
}

