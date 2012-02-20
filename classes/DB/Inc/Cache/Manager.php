<?php

class DB_Inc_Cache_Manager {
	
	const DB_INC_CACHE_MANAGER_CONST = 'DB_DBAL_CACHE_';
	private $_doCache = false;
	private $_cache = array();
	private $_files = array();
	private $_pkeys = array();
	
	public function __construct()
	{
		if(function_exists('apc_fetch')) {
			$this->_doCache = true;
		}
	}
	
	public function getCache($db, $table, $key)
	{
		if(!$this->_doCache) { 
			if(isset($this->_cache[$db][$table][$key])) {
				return;
			}
			return false;
		}
		
		$key = DB_INC_CACHE_MANAGER_CONST . $db . '_' . $table;
		$r = apc_fetch($key);
		
		if($r && isset($r[$key])) {
			return $r[$key];
		}
		
		return false;
	}
	
	public function setCache($db, $table, $key, $data)
	{
		if(!$this->_doCache) { 
			$this->_cache[$db][$table][$key] = $data;
			return false; 
		}
		
		$key = DB_INC_CACHE_MANAGER_CONST . $db . '_' . $table;
		$r = apc_fetch($key);
		
		if($r) {
			$r[$key] = $data;
		}
		
		apc_store($key, $r);
	}
	
	public function clearCache($db, $table)
	{
		if(!$this->_doCache) { 
			if(isset($this->_cache[$db][$table])) {
				unset($this->_cache[$db][$table]);
				return;
			}
			return false; 
		}
		
		$key = DB_INC_CACHE_MANAGER_CONST . $db . '_' . $table;
		apc_delete($key);
	}
	
	public function setPrimaryKey($db, $table, $column) {
		if(!$this->_doCache) { 
			$this->_pkeys[$db][$table] = $column;
			return false; 
		}
		
		$key = DB_INC_CACHE_MANAGER_CONST . $db . '_' . $table . '_PrimaryKey';
		
		apc_store($key, $column);
	}
	
	public function getPrimaryKey($db, $table)
	{
		if(!$this->_doCache) { 
			if(isset($this->_pkeys[$db][$table])) {
				return $this->_pkeys[$db][$table];
			}
			return false; 
		}
		
		$key = DB_INC_CACHE_MANAGER_CONST . $db . '_' . $table . '_PrimaryKey';
		$column = apc_fetch($key);
		
		return $column;
	}
	
	public function setFileStatus($db, $table, $status)
	{
		if(!$this->_doCache) { 
			$this->_files[$db][$table] = $status;
			return false; 
		}
		
		$key = DB_INC_CACHE_MANAGER_CONST . $db . '_' . $table . '_File';
		apc_store($key, $status);
	}
	
	public function getFileStatus($db, $table)
	{
		if(!$this->_doCache) { 
			if(isset($this->_files[$db][$table])) {
				return $this->_files[$db][$table];
			}
			return false; 
		}
		
		$key = DB_INC_CACHE_MANAGER_CONST . $db . '_' . $table . '_File';
		$status = apc_fetch($key);
		
		return $status;
	}
	
	
	
	public function makeKey($input)
	{
		return md5($input);
	}
}