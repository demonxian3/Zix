<?php
namespace Model;

use Common\BaseMysqlModel;

class Appointment extends BaseMysqlModel{

 	public $table = 'appointment';
 	public $columns = ['id', 'openid', 'truename', 'phone', 'date', 'time', 'remark', 'type', 'class', 'item'];

 	function __construct() {
 		parent::__construct($this->table, $this->columns, $this->table);
 	}

} 