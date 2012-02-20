<?php
class DB_Inc_Abstracts_Models extends DB_Inc_Abstracts_Common {
	
	public function __construct($data) {
		
		$this->_data['new'] = $data->getAllData();
		$this->_data['old'] = $data->getAllData('old');
		$this->_data['changed'] = $data->getAllData('changed');
		
		if(method_exists($this, 'init')) {
			$this->init();
		}
	}
	
	public function returnData() {
		return $this->getAllData();
	}
}