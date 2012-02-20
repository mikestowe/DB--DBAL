<?php
class DB_Inc_Abstracts_DataHandler
{	
	public function fileStatus() {
		$status = DB::cacheManager()->getFileStatus($this->_database, $this->_query['table']['name']);
		if(!$status) {
			$file = DB::getPath() . '/DB/Models/'.strtolower($this->_identifier).'/'.strtolower($this->_database).'/'.strtolower($this->_query['table']['name']).'Table.php';
			
			if(file_exists($file)) {
				require_once($file);
				$status = 'exists';
			} else {
				$status = 'doesntexist';
			}
			DB::cacheManager()->setFileStatus($this->_database, $this->_query['table']['name'], $status);
		}
		
		if($status == 'exists') {
			return true;
		}
		
		return false;
	}
	
	public function className()
	{
		return 'DB_Models_' . $this->_identifier . '_' . $this->_database . '_' . strtolower($this->_query['table']['name']) . 'Table';
	}
	
	public function doMethod($method)
	{
		$className = self::className();
		if(method_exists($className, $method)) {
			$database = $this->_database;
			$randKey = time();
			$this->saveConnectionAs('__internals__DataHandler_' . $randKey . $database . $this->_query['table']['name']);
			$obj = new $className($this);
			$obj->$method();
			$this->_data['new'] = $obj->returnData();
			$this->useConnection('__internals__DataHandler_' . $randKey . $database . $this->_query['table']['name']);
			$this->db($database);
		}
		
	}
	
	public function __call($method, $parameters) {
		
		if(!(substr($method, 0, 6) == 'handle')) {
			throw new Exception('Method '. $method .' Does Not Exist!');
		}
		
		if(!self::fileStatus()) { return; }
		
		return self::doMethod(substr($method, 6));
	}
}