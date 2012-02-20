<?php
class DB_Inc_Abstracts_Internals extends DB_Inc_Abstracts_DataHandler
{
    protected $_identifier;
    protected $_database;
    
    protected $_data = array(
        'new'       =>  array(),
        'old'       =>  array(),
        'changed'   =>  array(),
        'internals' =>  array(
    						'limit' => false,
    						'insertId' => false,
    						'affectedRows' => 0,
    						'count' => 0
    					),
    );
    
    protected $_query = array(
        'type'      =>  'select',
        'select'    =>  '*',
        'where'     =>  array(),
        'whereType' =>  '',
        'join'      =>  array(),
    	'data'		=>  array(),
    );
    protected $_resource;

    public function __construct($function, $connection = false, $database = null)
    {
        $this->_identifier = $function;
        
        if($connection) {
            $this->saveConnection($connection);
            
            if($database) {
                $this->selectDB($database);
            }
        }
        
        
    }

    public function saveConnection($connection) {
        DB::getInstance()->saveConnection($this->_identifier, 'active', $connection);
    }
    
    public function getConnection($name = null) {
        if($name) {
            return DB::getInstance()->getConnection($this->_identifier, $name);
        }
        
        return DB::getInstance()->getConnection($this->_identifier, 'active');
    }
    
    public function saveConnectionAs($name)
    {
        DB::getInstance()->saveConnection($this->_identifier, $name, $this->getConnection());
    }
    
    public function useConnection($name)
    {
        $conn = $this->getConnection($name);
        $this->saveConnection($conn);
        return $conn;
        
    }
    
    public function closeConnection($name = null)
    {
        if(!$name) {
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
        if($this->getConnection()) {
            return true;
        }
        return false;
    }
    
    public function saveData($key, $value, $type = 'new')
    {
        // Should be: new, old, changed, internals
        $this->_data[$type][$key] = $value;
        return $this;
    }
    
    public function getData($key, $type = 'new')
    {
    	if(!isset($this->_data[$type][$key])) {
    		return null;
    	}
        return $this->_data[$type][$key];
    }
    
    public function getValue($key, $type = 'new')
    {
        return $this->getData($key, $type);
    }
    
    public function getAllData($type = 'new', $object = false)
    {
        if($object) {
            return (object) $this->_data[$type];
        }
        
        return $this->_data[$type];
    }
    
    public function setQuerySQL($sql)
    {
        $this->saveData('querySQL', $sql, 'internals');
        return $this;
    }
    
    public function getQuerySQL()
    {
        return $this->getData('querySQL', 'internals');
    }
    
    public function setInsertId($id)
    {
    	if(!is_int($id)) { throw new InvalidArgumentException('setInsertId expects ID to be an integer'); }
        $this->saveData('insertId', $id, 'internals');
        return $this;
    }
    
    public function getInsertId()
    {
        return $this->getData('insertId', 'internals');
    }
    
    public function setAffectedRows($numRows)
    {
    	if(!is_int($numRows)) { throw new InvalidArgumentException('setAffectedRows expects numRows to be an integer'); }
        $this->saveData('affectedRows', $numRows, 'internals');
        return $this;
    }
    
    public function getAffectedRows()
    {
        return $this->getData('affectedRows', 'internals');
    }
    
    public function setCount($numRows)
    {
    	if(!is_int($numRows)) { throw new InvalidArgumentException('setCount expects numRows to be an integer'); }
        $this->saveData('count', $numRows, 'internals');
        return $this;
    }
    
    public function getCount()
    {
        return $this->getData('count', 'internals');
    }
    
    public function showConnections()
    {
        var_dump(DB::getInstance()->getConnections($this->_identifier));
    }
    
    public function newDataSet($primaryKey, $data) {
    	$obj = clone $this;
    	foreach($data as $k => $v) {
    		$obj->$k = $v;
    		$obj->saveData($k, $v, 'old');
    	}
    	
    	if($primaryKey === false) {
    		$obj->_query['noPrimary'] = true;
    	} else {
    		$obj->resetWhere()->where($primaryKey .' = ?', $data->$primaryKey);
    	}
    	
    	return $obj;
    }
    
    public function getCache($query) {
    	return DB::cacheManager()->getCache($this->_database, $this->_query['table']['name'], DB::cacheManager()->makeKey($query));
    }
    
    public function setCache($query, $data) {
    	return DB::cacheManager()->setCache($this->_database, $this->_query['table']['name'], DB::cacheManager()->makeKey($query), $data);
    }
    
    public function getPrimaryKey() {
    	return DB::cacheManager()->getPrimaryKey($this->_database, $this->_query['table']['name']);
    }
    
    public function setPrimaryKey($column) {
    	return DB::cacheManager()->setPrimaryKey($this->_database, $this->_query['table']['name'], $column);
    }
    
    public function clearCache() {
    	DB::cacheManager()->clearCache($this->_database, $this->_query['table']['name']);
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
}