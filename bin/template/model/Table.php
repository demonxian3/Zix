<?php
namespace Model;

use Common\BaseMysqlModel;

class Table extends BaseMysqlModel{

 	public $table = 'table';
 	public $columns = ['id', 'username'];

 	function __construct() {
 		parent::__construct($this->table, $this->columns, $this->table);
 	}

} 