<?php
/**
 *
 * MySQL Router
 * Determines whether or not to use MySQLi
 * and reroutes all data accordingly
 * 
 * @author mstowe
 *
 */
class DB_MySQL
{
	private $_instance;
	
	public function __construct($function, $connection = false, $database = null) {
		if(function_exists('mysqli_query')) {
			require_once('MySQLi.php');
			$this->_instance = new DB_MySQLi($function, $connection, $database);
		} else {
			require_once('MySQLc.php');
			$this->_instance = new DB_MySQLc($function, $connection, $database);
		}
	}
	
	public function __call($function, $parameters) {
		return call_user_func_array(array($this->_instance, $function), $parameters);
	}
	
	public function __get($key) {
		return $this->_instance->$key;
	}
	
	public function __set($key, $value) {
		return $this->_instance->$key = $value;
	}
	
	public function __isset($key) {
		return isset($this->_instance->$key);
	}
	
	public function __unset($key) {
		unset($this->_instance->$key);
	}
}
?>