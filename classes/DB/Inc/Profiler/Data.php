<?php
class DB_Inc_Profiler_Data {
    private $_query;
    private $_time;
    private $_memory;

    public function __construct($query, $time, $memory) {
        $this->_query = $query;
        $this->_time = $time;
        $this->_memory = $memory;
    }

    /*
     * LONG NAMES
     */
    public function getQuery() {
        return $this->_query;
    }

    public function getExecutionTime() {
        return $this->_time;
    }

    public function getMemoryUsed() {
        return $this->_memory;
    }

    public function getMemoryUsage() {
        return $this->_memory;
    }

    /*
     * SHORT NAMES
     */
    public function query() {
        return $this->_query;
    }

    public function time() {
        return $this->_time;
    }

    public function memory() {
        return $this->_memory;
    }

}
