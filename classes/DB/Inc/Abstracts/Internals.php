<?php
class DB_Inc_Abstracts_Internals extends DB_Inc_Abstracts_DataHandler {
    protected $_identifier;
    protected $_database;

    protected $_data = array('new' => array(), 'old' => array(), 'changed' => array(), 'internals' => array('limit' => false, 'insertId' => false, 'affectedRows' => 0, 'count' => 0), );

    protected $_query = array('type' => 'select', 'select' => '*', 'where' => array(), 'whereType' => '', 'join' => array(), 'data' => array(), );
    protected $_resource;
    private $_profiler;

    public function __construct($function, $connection = false, $database = null) {
        $this->_identifier = $function;

        if ($connection) {
            $this->saveConnection($connection);

            if ($database) {
                $this->selectDB($database);
            }
        }

    }

    public function saveConnection($connection) {
        DB::getInstance()->saveConnection($this->_identifier, 'active', $connection);
    }

    public function getConnection($name = null) {
        if ($name) {
            return DB::getInstance()->getConnection($this->_identifier, $name);
        }

        return DB::getInstance()->getConnection($this->_identifier, 'active');
    }

    public function saveConnectionAs($name) {
        DB::getInstance()->saveConnection($this->_identifier, $name, $this->getConnection());
    }

    public function useConnection($name) {
        $conn = $this->getConnection($name);
        $this->saveConnection($conn);
        return $conn;

    }

    public function closeConnection($name = null) {
        if (!$name) {
            $name = 'active';
        }

        DB::getInstance()->removeConnection($this->_identifier, $name);
    }

    public function saveDatabase($database) {
        $this->_database = $database;
    }

    public function getDatabase() {
        return $this->_database;
    }

    public function isConnected() {
        if ($this->getConnection()) {
            return true;
        }
        return false;
    }

    public function saveData($key, $value, $type = 'new') {
        // Should be: new, old, changed, internals
        $this->_data[$type][$key] = $value;
        return $this;
    }

    public function getData($key, $type = 'new') {
        if (!isset($this->_data[$type][$key])) {
            return null;
        }
        return $this->_data[$type][$key];
    }

    public function getValue($key, $type = 'new') {
        return $this->getData($key, $type);
    }

    public function getAllData($type = 'new', $object = false) {
        if ($object) {
            return (object)$this->_data[$type];
        }

        return $this->_data[$type];
    }

    protected function setQuerySQL($sql) {
        $this->saveData('querySQL', $sql, 'internals');
        return $this;
    }

    public function getQuerySQL() {
        return $this->getData('querySQL', 'internals');
    }

    protected function setInsertId($id) {
        if (!is_int($id)) {
            throw new InvalidArgumentException('setInsertId expects ID to be an integer');
        }
        $this->saveData('insertId', $id, 'internals');
        return $this;
    }

    public function getInsertId() {
        return $this->getData('insertId', 'internals');
    }

    protected function setAffectedRows($numRows) {
        if (!is_int($numRows)) {
            throw new InvalidArgumentException('setAffectedRows expects numRows to be an integer');
        }
        $this->saveData('affectedRows', $numRows, 'internals');
        return $this;
    }

    public function getAffectedRows() {
        return $this->getData('affectedRows', 'internals');
    }

    protected function setCount($numRows) {
        if (!is_int($numRows)) {
            throw new InvalidArgumentException('setCount expects numRows to be an integer');
        }
        $this->saveData('count', $numRows, 'internals');
        return $this;
    }

    public function getCount() {
        return $this->getData('count', 'internals');
    }

    public function showConnections() {
        var_dump(DB::getInstance()->getConnections($this->_identifier));
    }

    protected function newDataSet($primaryKey, $data) {
        $obj = clone $this;
        foreach ($data as $k => $v) {
            $obj->$k = $v;
            $obj->saveData($k, $v, 'old');
        }

        if ($primaryKey === false) {
            $obj->_query['noPrimary'] = true;
        } else {
            $obj->removeWhere()->where($primaryKey . ' = ?', $data->$primaryKey);
        }

        return $obj;
    }

    protected function getCache($query) {
        return DB::cacheManager()->getCache($this->_database, $this->_query['table']['name'], DB::cacheManager()->makeKey($query));
    }

    protected function setCache($query, $data) {
        return DB::cacheManager()->setCache($this->_database, $this->_query['table']['name'], DB::cacheManager()->makeKey($query), $data);
    }

    protected function getCachedPrimaryKey() {
        return DB::cacheManager()->getPrimaryKey($this->_database, $this->_query['table']['name']);
    }

    protected function setCachedPrimaryKey($column) {
        return DB::cacheManager()->setPrimaryKey($this->_database, $this->_query['table']['name'], $column);
    }

    public function setPrimaryKey($column) {
        $this->setCachedPrimaryKey($column);
        return $this;
    }

    public function clearCache() {
        DB::cacheManager()->clearCache($this->_database, $this->_query['table']['name']);
    }

    protected function profilerStartRecording() {

        if (!DB::profiler()->getStatus($this->_identifier)) {
            return;
        }
        $this->_profiler['time'] = microtime(true);
        $this->_profiler['memory'] = memory_get_usage();
    }

    protected function profilerEndRecording($query) {
        if (!DB::profiler()->getStatus($this->_identifier)) {
            return;
        }
        DB::profiler()->addToStack($this->_identifier, $query, (microtime(true) - $this->_profiler['time']), (memory_get_usage() - $this->_profiler['memory']));
    }

    public function profilerStart() {
        DB::profiler()->setStatus($this->_identifier, true);
        DB::profiler()->clearStack($this->_identifier);
        return $this;
    }

    public function profilerEnd() {
        DB::profiler()->setStatus($this->_identifier, false);
        return $this;
    }

    public function profilerShow() {
        return DB::profiler()->getStack($this->_identifier);
    }

    public function profilerShowLargerThan($memory) {
        return DB::profiler()->getQueriesLargerThan($this->_identifier, $memory);
    }

    public function profilerShowLongerThan($seconds) {
        return DB::profiler()->getQueriesLongerThan($this->_identifier, $seconds);
    }

    public function __get($key) {
        return $this->getData($key);
    }

    public function __set($key, $value) {
        $this->saveData($key, $value);
    }

    public function __isset($key) {
        return isset($this->_data['new'][$key]);
    }

    public function __unset($key) {
        unset($this->_data['new'][$key]);
    }

    /**
     * Magic Find Methods
     */
    public function __call($function, $parameters) {
        if (preg_match('/^(find(One)?)/', $function, $do)) {
            preg_match_all('#(AndBy|OrBy|By)(.+?)(?=AndBy|OrBy|$)#', $function, $matches);
            $checks = count($matches[0]);
            if ($checks != count($parameters)) {
                throw new Exception(sprintf('Method %s expects %d arguments, %d given.', $function, $checks, count($parameters)));
            }

            for ($i = 0; $i != $checks; $i++) {
                if ($i == 0) {
                    $this->where(strtolower($matches[2][$i]) . ' = "?"', $parameters[$i]);
                } elseif ($matches[1][$i] == 'AndBy') {
                    $this->andWhere(strtolower($matches[2][$i]) . ' = "?"', $parameters[$i]);
                } elseif ($matches[1][$i] == 'OrBy') {
                    $this->orWhere(strtolower($matches[2][$i]) . ' = "?"', $parameters[$i]);
                } else {
                    throw new Exception('Method ' . $function . ' does not exist');
                }
            }

            if ($do[0] == 'findOne') {
                return $this->fetchOne();
            } else {
                return $this->fetch();
            }
        }

        parent::__call($function, $parameters);
    }

}
