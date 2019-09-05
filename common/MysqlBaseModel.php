<?php
namespace common;

use lib\Medoo;

class MysqlBaseModel  {

    public function build(){
        $this->db = new Medoo([
            'database_type' => 'mysql',
            'server'        => 'localhost',
            'database_name' => Config::db_database,
            'username'      => Config::db_username,
            'password'      => Config::db_password,
        ]);
        return $this->db;
    }
}
