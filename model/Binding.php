<?php
namespace Model;

use Common\BaseMysqlModel;

class Binding extends BaseMysqlModel{

 	public $table = 'binding';
 	public $columns = ['id', 'cid', 'tid'];

 	function __construct() {
 		parent::__construct($this->table, $this->columns, $this->table);
 	}

}
