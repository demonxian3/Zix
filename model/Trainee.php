<?php
namespace Model;

use Common\BaseMysqlModel;

class Trainee extends BaseMysqlModel{

 	public $table = 'trainee';
 	public $columns = ['id', 'openid', 'truename', 'phone', 'class'];

 	function __construct() {
 		parent::__construct($this->table, $this->columns, $this->table);
 	}

}
