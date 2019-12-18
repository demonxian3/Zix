<?php
namespace Model;

use Common\BaseMysqlModel;

class Coach extends BaseMysqlModel{

 	public $table = 'coach';
 	public $columns = ['id', 'openid', 'truename', 'phone'];

 	function __construct() {
 		parent::__construct($this->table, $this->columns, $this->table);
 	}

}
