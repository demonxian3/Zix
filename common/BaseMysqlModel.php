<?php
namespace Common;

class BaseMysqlModel {
    private $table = '';
    private $columns = '*';
    private $conditions = [];
    private $debug = false;
    private $joinQueue = [];
    private $joinColumns = [];
    private $pagination = [];

    public function __construct(string $table, array $columns, string $channel = 'default')
    {
        global $_DI;
        $this->mysql = $_DI['mysql'];
        $this->logger = $_DI['logger'];

        $this->setTable($table);
        $this->setColumn($columns);
        $this->setChannel($channel);
    }

    public function recordError(string $method): void
    {
        $error = $this->mysql->error();
        if ($error[2] !== null) {
            $this->logger->error('mysql:'.$method, $error);
        }
    }

    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function setColumn(array $columns): self
    {
        foreach ($columns as $idx => $column) {
            if (is_string($column) && strpos($column, '.') === false) {
                $columns[$idx] = $this->table .'.'. $column;
            }
        }
        $this->columns = $columns;
        return $this;
    }

    public function setChannel(string $channel): self
    {
        $this->logger->setChannel($channel);
        return $this;
    }

    public function setWhere(array $conditions = []): self
    {
        if ($conditions) $this->conditions = $conditions;
        return $this;
    }

    public function addWhere(array $conditions = []): self
    {
        if ($conditions) $this->conditions = $this->conditions  + $conditions;
        return $this;
    }


    public function paginate($page=1, $size=10):self
    {
        $this->pagination = [($page-1)*$size, $size];
        return $this;
    }

    public function debug(bool $debug = true): self
    {
        $this->debug = $debug;
        return $this;
    }

    public function select($where = [], $columns = []): array
    {
        if ($where) $this->setWhere($where);
        if ($columns) $this->setColumn($columns);
        if ($this->pagination) {
            $this->conditions['LIMIT'] = $this->pagination;
            $this->pagination = [];
        }

        if ($this->debug) {
            $this->mysql->debug()->select($this->table, $this->columns, $this->conditions);
            $this->debug = false;
            return [];
        } 

        $data = $this->mysql->select($this->table, $this->columns, $this->conditions);
        if (!$data) $this->recordError(__METHOD__);
        return $data;
    }


    public function insert(array $data): int
    {
        if ($this->debug) {
            $res = $this->mysql->debug()->insert($this->table, $data);
            $this->debug = false;
            return 0;
        } 

        $res = $this->mysql->insert($this->table, $data);
        $count = $res->rowCount();
        if (!$count) $this->recordError(__METHOD__);
        return $count;
    }

    public function update(array $data, array $where = []): int
    {
        if ($where) $this->setWhere($where);

        if ($this->debug) {
            $res = $this->mysql->debug()->update($this->table, $data, $this->conditions);
            $this->debug = false;
            return 0;
        }

        $res = $this->mysql->update($this->table, $data, $this->conditions);
        $count = $res->rowCount();
        if (!$count) $this->recordError(__METHOD__);
        return $count;
    }

    public function change(array $data, array $where): int
    {
        if ($this->has($where)) {
            return $this->update($data, $where);
        } else {
            return $this->insert($data + $where);
        }
    }

    public function delete(array $where): int
    {
        if ($where) $this->setWhere($where);
        if ($this->debug) {
            $res = $this->mysql->debug()->delete($this->table, $this->conditions);
            $this->debug = false;
            return 0;
        }
        $res = $this->mysql->delete($this->table, $this->conditions);
        $count = $res->rowCount();

        if (!$count) $this->recordError(__METHOD__);
        return $count;
    }

    public function has(array $where): bool
    {
        $this->setWhere($where);
        return $this->select() ? true : false;
    }


    public function lastSql(): string
    {
        return $this->mysql->last();
    }

    public function lastId(): int
    {
        return $this->mysql->id();
    }

    public function log(): array
    {
        return $this->mysql->log();
    }

    public function exportColumns(): array
    {
        return $this->columns;
    }

    public function exportTable(): String
    {
        return $this->table;
    }

    public function leftJoin(BaseMysqlModel $Model, array $on, array $alias = []): self
    {
        return $this->join('[>]', $Model, $on, $alias);
    }

    public function rightJoin(BaseMysqlModel $Model, array $on, array $alias = []): self
    {
        return $this->join('[<]', $Model, $on, $alias);
    }

    public function fullJoin(BaseMysqlModel $Model, array $on, array $alias = []): self
    {
        return $this->join('[<>]', $Model, $on, $alias);
    }

    public function innerJoin(BaseMysqlModel $Model, array $on, array $alias = []): self
    {
        return $this->join('[><]', $Model, $on, $alias);
    }

    public function join(string $mode, BaseMysqlModel $Model, array $on, array $alias = []): self
    {
        $table = $Model->exportTable();
        $columns = $Model->exportColumns();
        $connect = $mode . $table;

        if ($alias) {
            foreach ($alias as $key => $value) {
                if (!strpos($key, '.')){
                    $fullname = $table .'.'. $key;
                } else {
                    $fullname = $key;
                }
                foreach ($columns as $idx => $column) {
                    if ($fullname === $column) {
                        $columns[$idx] .= (' ('. $value .')');
                        break;
                    }
                }
            }
        }

        $this->joinQueue[$connect] = $on;
        $this->joinColumns = array_merge($this->joinColumns, $columns);
        return $this;
    }

    public function show( array $where = []): array
    {
        if ($where) $this->setWhere($where);
        $columns = array_merge($this->columns, $this->joinColumns);

        if ($this->debug) {
            $this->mysql->debug()->select($this->table, $this->joinQueue, $columns, $where);
            return [];
        }

        if ($this->pagination) {
            $this->conditions['LIMIT'] = $this->pagination;
            $this->pagination = [];
        }
        
        $data = $this->mysql->select($this->table, $this->joinQueue, $columns, $this->conditions);
        if (!$data) $this->recordError(__METHOD__);
        return $data;
    }

    
    public function count( array $where = []): int
    {
        if ($where) $this->setWhere($where);
        $columns = array_merge($this->columns, $this->joinColumns);

        if ($this->debug) {
            if ($this->joinQueue){
                $this->mysql->debug()->count($this->table, $this->joinQueue, $columns, $where);
                
            } else {
                $this->mysql->debug()->count($this->table, $this->conditions);
            }
            return [];
        }

        if ($this->joinQueue){
            $data = $this->mysql->count($this->table, $this->joinQueue, $columns, $this->conditions);
            
        }else{
            $data = $this->mysql->count($this->table, $this->conditions);
        }
         
        if (!$data) $this->recordError(__METHOD__);
        return $data;
    }
}
