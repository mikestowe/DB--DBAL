<?php
class DB_Firebird extends DB_Inc_Abstracts_Common implements DB_Inc_Interfaces_Database
{
    public function init()
    {
        /** USE THIS AS YOUR CONSTRUCT FUNCTION **/
    }
    
    public function connect()
    {
        $arg = func_get_args();
        if(count($arg) == 1) {
            $arg = $arg[0];
        }
        
        if(count($arg) == 3) {
            $connection = ibase_connect($arg[0], $arg[1]. $arg[2]);
            $this->selectDB('generic');
        } else {
            throw new InvalidArgumentException('Invalid Number of Arguments');
        }

        parent::saveConnection($connection);
        return $this;
    }
    
    public function selectDB($database)
    {
    	if($database != 'generic') {
    		throw new Exception('FireBird does not accept database selection.');
    	}
        parent::saveDatabase($database);
        return $this;
    }
    
    function quote($input) {
        return str_replace("'", "''", $input);
    }
    
    public function error()
    {
        return ibase_errmsg();
    }
    
    public function execute($getOldData=true) {
		if(!parent::getAllData('old') && $getOldData && $this->_query['type'] == 'update') {
			$tmp = clone $this;
			$tmp->select('*');
			$a = $tmp->fetchOne();
			foreach($a as $key => $value) {
				parent::saveData($key, $value, 'old');
			}
		}
		
		$query = ($this->_query['type'] == 'insert'?'INSERT INTO':'UPDATE').' `'.$this->_query['table']['name'].'` SET';
		
	    if(isset($this->_query['smart_update']) && $this->_query['smart_update'] && $this->_query['type'] == 'update') {
	    	$this->_query['data'] = array();
			foreach($this->getAllData() as $k=>$v) {
				if($v != parent::getData($k, 'old')) {
					$this->_query['data'][$k] = $v;
				}
			}
		}
		
		if(is_array($this->_query['data']) && count($this->_query['data']) > 0) {
			foreach($this->_query['data'] as $k => $v) {
				$query .= ' '.$k.' = "'.$this->quote($v).'",';
				if($getOldData && parent::getData($k, 'old') != $v) {
					parent::saveData($k, array('old' => parent::getData($k, 'old'), 'new' => $v), 'changed');
				}
			}
			$query = substr($query, 0, -1);
		} elseif (is_string($this->_query['data'])) {
			$query .= ' '.$this->_query['data'];
		} else {
			return false;
		}
		
		if($this->_query['type'] != 'insert') {
			if(is_array($this->_query['where']) && $this->_query['where']) {
				$query .= ' WHERE';
					foreach($this->_query['where'] as $where) {
						$query .= ' '.$where;
					}
				}
				
			if(isset($this->_query['limit']) && $this->_query['limit']) {
				$query .= ' LIMIT '. $this->_query['limit'];
			}
		}
		
		parent::setQuerySQL($query);
		$result = $this->doRawQuery($query);
		
		if($this->_query['type'] == 'insert') {
			parent::setInsertId(0);
		}
		
		parent::setAffectedRows(ibase_affected_rows(self::getConnection()));
		
		$this->clearCache();
		
		return $result;
		
	}
    
    public function dofetch() {
		$this->buildQuery();
		$cache = $this->getCache($this->getQuerySql());
		if($cache) {
			parent::setCount(count($cache));
			return $cache;
		}
		$this->_resource = $this->doRawQuery($this->getQuerySql());
		
		if($this->_query['limit'] == 1) {
			$data = ibase_fetch_assoc($this->_resource);
			if($data) {
				foreach($data as $dataKey => $dataValue) {
					$this->saveData($dataKey, $dataValue);
					$this->saveData($dataKey, $dataValue, 'old');
				}
				parent::setCount(1);
				$this->setCache($this->getQuerySql(), $data);
			}
		} else {
			$primaryKey = $this->getPrimaryKey();
			if(!$primaryKey) {
				$r = $this->doRawQuery('SHOW INDEX FROM `' . $this->_query['table']['name'] . '` WHERE Key_name = "PRIMARY"');
				if(ibase_num_rows($r) == 1) {
					$o = ibase_fetch_object($r);
					$primaryKey = $o->Column_name;
					$this->setPrimaryKey($primaryKey);
				}
			}
			
			$result = array();
			while($tmp = ibase_fetch_object($this->_resource)) {
				$result[] = parent::newDataSet($primaryKey, $tmp);
			}
			
			parent::setCount(count($result));
			$this->setCache($this->getQuerySql(), $result);
			return $result;
		}
	}
	
	public function buildQuery() {
		if(!isset($this->query)) {
			$query = 'SELECT ';
			
			if(isset($this->_query['limit']) && $this->_query['limit']) {
				$limit = explode(',', $this->_query['limit']);
				if(isset($limit[1])) {
					$query .= 'FIRST ' . $limit[1] . ' SKIP ' . $limit[0] . ' ';
				} else {
					$query .= 'FIRST ' . $limit[0] . ' ';
				}
			}
			
			$query .= $this->_query['select'].' FROM `'.$this->_query['table']['name'].'`';
			if(!is_null($this->_query['table']['shortname'])) { $query .= ' as `'.$this->_query['table']['shortname'].'`'; }
			
			if(is_array($this->_query['join'])) {
				foreach($this->_query['join'] as $join) {
					$query .= ' '.$join[3].' join `'.$join[0].'` as '.$join[1];
					if(strstr($join[2],'=')) {
						$query .= ' ON '.$join[2];
					} else {
						$query .= ' USING('.$join[2].')';
					}
				}
			}
			
			if(is_array($this->_query['where']) && $this->_query['where']) {
			$query .= ' WHERE';
				foreach($this->_query['where'] as $where) {
					$query .= ' '.$where;
				}
			}
			
			if(isset($this->_query['groupby']) && is_array($this->_query['groupby'])) {
			    $query .= ' GROUP BY';
				foreach($this->_query['groupby'] as $groupby) {
					$query .= ' '.$groupby.',';
				}
			    $query = substr($query,0,-1);
			}
			
			
		    if(isset($this->_query['orderby']) && is_array($this->_query['orderby'])) {
			    $query .= ' ORDER BY';
			    foreach($this->_query['orderby'] as $orderby) {
					$query .= ' '.$orderby.',';
			    }
			    $query = substr($query,0,-1);	
			}
			
			$this->setQuerySQL($query);
		}
	}
	
	public function doRawQuery($query) {
		return ibase_query(self::getConnection(), $query);
	}
	
	/* TRANSACTION FUNCTIONS */
	
	public function startTransaction() {
		ibase_trans(self::getConnection());
	}
	
	public function rollback() {
		ibase_rollback(self::getConnection());
	}
	
	public function commit() {
		ibase_commit(self::getConnection());
	}
}