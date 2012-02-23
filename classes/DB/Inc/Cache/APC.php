<?php
class DB_Inc_Cache_APC implements DB_Inc_Cache_Interface {
	public function getCache($db, $table, $key)
	{
		$key = DB_Inc_Cache_Manager::keyPrefix . $db . '_' . $table;
		$r = apc_fetch($key);
		
		if($r && isset($r[$key])) {
			return $r[$key];
		}
		
		return false;
	}
	
	public function setCache($db, $table, $key, $data)
	{
		$key = DB_Inc_Cache_Manager::keyPrefix . $db . '_' . $table;
		$r = apc_fetch($key);
		
		if($r) {
			$r[$key] = $data;
		}
		
		apc_store($key, $r);
	}
	
	public function clearCache($db, $table)
	{
		$key = DB_Inc_Cache_Manager::keyPrefix . $db . '_' . $table;
		apc_delete($key);
	}
	
	public function setPrimaryKey($db, $table, $column) {
		if(!$this->_doCache()) { 
			$this->_pkeys[$db][$table] = $column;
			return false; 
		}
		
		$key = DB_Inc_Cache_Manager::keyPrefix . $db . '_' . $table . '_PrimaryKey';
		
		apc_store($key, $column);
	}
	
	public function getPrimaryKey($db, $table)
	{
		$key = DB_Inc_Cache_Manager::keyPrefix . $db . '_' . $table . '_PrimaryKey';
		$column = apc_fetch($key);
		
		return $column;
	}
	
	public function setFileStatus($db, $table, $status)
	{
		if(!$this->_doCache()) { 
			$this->_files[$db][$table] = $status;
			return false; 
		}
		
		$key = DB_Inc_Cache_Manager::keyPrefix. $db . '_' . $table . '_File';
		apc_store($key, $status);
	}
	
	public function getFileStatus($db, $table)
	{
		$key = DB_Inc_Cache_Manager::keyPrefix . $db . '_' . $table . '_File';
		$status = apc_fetch($key);
		
		return $status;
	}
}