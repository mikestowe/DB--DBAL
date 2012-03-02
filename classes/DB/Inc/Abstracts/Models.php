<?php
class DB_Inc_Abstracts_Models extends DB_Inc_Abstracts_Common {

    public function __construct($data) {
        // Used for Setup Method
        $this->_database = $data->_database;
        $this->_query['table']['name'] = $data->_query['table']['name'];

        // Used for Data Access and Manipulation
        $this->_data['new'] = $data->getAllData();
        $this->_data['old'] = $data->getAllData('old');
        $this->_data['changed'] = $data->getAllData('changed');

        // Construct for Any Action
        if (method_exists($this, 'init')) {
            $this->init();
        }
    }

    public function returnData() {
        return $this->getAllData();
    }

}
