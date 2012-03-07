<?php
class DB_Inc_Abstracts_Common extends DB_Inc_Abstracts_Internals implements DB_Inc_Interfaces_Common {
    public function db($database) {
        $this->selectDB($database);

        return $this;
    }

    public function table($table, $shortname = null) {
        $this->_query['table']['name'] = $table;
        $this->_query['table']['shortname'] = $shortname;
        $this->handleSetup();
        return $this;
    }

    public function select() {
        $this->_resource = false;

        if (func_num_args() == 0) {
            $this->_query['select'] = '*';
        } elseif (func_num_args() == 1) {
            $this->_query['select'] = func_get_arg(0);
            if (is_array($this->_query['select'])) {
                $this->_query['select'] = implode(', ', $this->_query['select']);
            }
        } else {
            $this->_query['select'] = implode(', ', func_get_args());
        }
        $this->_query['type'] = 'select';
        return $this;
    }

    public function insert(array $data) {
        $this->_query['type'] = 'insert';
        $this->_query['data'] = $data;
        $this->_query['smart_update'] = false;
        return $this;
    }

    public function update(array $data) {
        $this->_query['type'] = 'update';
        $this->_query['data'] = $data;
        $this->_query['smart_update'] = false;
        return $this;
    }

    public function delete() {
        $this->_query['type'] = 'delete';
        return $this;
    }

    public function raw($query) {
        $this->_query['type'] = 'raw';
        $this->_query['query'] = $query;
        return $this;
    }

    public function join($table, $nicename, $on, $type = 'inner') {
        $this->_query['join'][] = array($table, $nicename, $on, $type);
        return $this;
    }

    public function innerjoin($table, $nicename, $on) {
        $this->join($table, $nicename, $on);
    }

    public function leftjoin($table, $nicename, $on) {
        $this->join($table, $nicename, $on, $type = 'left');
        return $this;
    }

    public function rightjoin($table, $nicename, $on) {
        $this->join($table, $nicename, $on, $type = 'right');
        return $this;
    }

    public function where() {
        if (func_num_args() == 1) {
            $this->_query['where'][] = $this->_query['whereType'] . func_get_arg(0);
        } else {
            $args = func_get_args();
            $statement = str_replace('%', '{*^pct!*}', array_shift($args));
            foreach ($args as $k => $v) {
                $args[$k] = $this->quote($v);
            }
            $this->_query['where'][] = $this->_query['whereType'] . str_replace('{*^pct!*}', '%', vsprintf(str_replace('?', '%s', $statement), $args));
        }
        $this->_query['whereType'] = '';
        return $this;
    }

    public function andWhere() {
        $args = func_get_args();
        $this->_query['whereType'] = 'AND ';
        return call_user_func_array(array(&$this, 'where'), $args);
    }

    public function orWhere() {
        $args = func_get_args();
        $this->_query['whereType'] = 'OR ';
        return call_user_func_array(array(&$this, 'where'), $args);
    }

    public function removeWhere() {
        $this->_query['where'] = array();
        $this->_query['whereType'] = '';
        return $this;
    }

    public function orderBy($orderby) {
        $this->_query['orderby'][] = $orderby;
        return $this;
    }
    
    public function removeOrderBy($orderby) {
        $this->_query['orderby'] = array();
        return $this;
    }

    public function groupBy($groupby) {
        $this->_query['groupby'][] = $groupby;
        return $this;
    }
    
    public function removeGroupBy($groupby) {
        $this->_query['groupby'] = array();
        return $this;
    }

    public function limit($start, $records = null) {
        if ($records == null || !$records) {
            $this->_query['limit'] = $start;
        } elseif ($start) {
            $this->_query['limit'] = $start . ',' . $records;
        } else {
            $this->_query['limit'] = false;
        }
        return $this;
    }

    public function removeLimit() {
        $this->limit(false);
        return $this;
    }

    function save($getOldData = null) {
        if (isset($this->_query['smart_update']) && $this->_query['smart_update'] && $this->_query['type'] == 'update' && isset($this->_query['noPrimary']) && $this->_query['noPrimary']) {
            throw new Exception('No Primary Key Defined for ' . $this->_query['table']['name'] . '.  Unable to update record without one');
        }

        $this->handlePreSave();
        if ($this->_query['type'] == 'insert') {
            $this->handlePreInsert();
        } else {
            $this->handlePreUpdate();
        }

        if (is_null($getOldData)) {
            if (isset($this->_query['smart_update'])) {
                $getOldData = $this->_query['smart_update'];
            } else {
                $getOldData = false;
            }
        }

        $this->execute($getOldData);

        $this->handlePostSave();
        if ($this->_query['type'] == 'insert') {
            $this->handlePostInsert();
        } else {
            $this->handlePostUpdate();
        }

        return $this;
    }

    public function query($query) {
        unset($this->_resource);
        $this->doRawQuery($query);
        return $this;
    }

    public function find($value) {
        return $this->where($this->getPrimaryKey() . ' = "?"', $value)->fetchOne();
    }

    public function fetch($start = null, $records = null) {
        $this->limit($start, $records);
        $this->_query['format'] = 'array';
        $this->_query['type'] = 'update';
        $this->_query['smart_update'] = true;
        $this->handlePreFetch();
        $data = $this->dofetch();
        $this->handlePostFetch();
        return $data;
    }

    function fetchAll() {
        $this->limit(false);
        $this->_query['format'] = 'array';
        $this->_query['type'] = 'update';
        $this->_query['smart_update'] = true;
        $this->handlePreFetch();
        $data = $this->dofetch();
        $this->handlePostFetch();
        return $data;
    }

    function fetchOne() {
        $this->limit(1);
        $this->_query['format'] = 'object';
        $this->_query['type'] = 'update';
        $this->_query['smart_update'] = true;
        $this->handlePreFetch();
        $this->dofetch();
        $this->handlePostFetch();
        return $this;
    }

    function oldData() {
        return $this->_data['old'];
    }

    function changedData($key = null, $oldornew = 'new') {
        if ($key != null) {
            if (!isset($this->_data['changed'][$key])) {
                return false;
            }
            return $this->_data['changed'][$key][$oldornew];
        }
        return $this->_data['changed'];
    }

    function insertId() {
        return $this->getInsertId();
    }

    function affectedRows() {
        return $this->getAffectedRows();
    }

    function count() {
        return $this->getCount();
    }

    // May be removed as it could cause confusion with query();
    function querySql() {
        return $this->getQuerySQL();
    }

}
