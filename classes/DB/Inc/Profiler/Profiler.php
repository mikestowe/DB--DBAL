<?php
class DB_Inc_Profiler_Profiler {
    private $_stack = array();
    private $_status = array();

    public function getStatus($db) {
        if (isset($this->_status[$db])) {
            return $this->_status[$db];
        }
        return false;
    }

    public function setStatus($db, $status) {
        $this->_status[$db] = $status;
    }

    public function getStack($db) {
        if (isset($this->_stack[$db])) {
            return $this->_stack[$db];
        }
        return false;
    }

    public function addToStack($db, $query, $time, $memory) {
        $t = new DB_Inc_Profiler_Data($query, $time, $memory);
        $this->_stack[$db][] = $t;
    }

    public function clearStack($db) {
        unset($this->_stack[$db]);
    }

    public function getQueriesLargerThan($db, $memory) {
        $queries = array();
        foreach (self::getStack($db) as $tmp) {
            if ($tmp->getMemoryUsed() > $memory) {
                $queries[] = $tmp;
            }
        }

        return $queries;
    }

    public function getQueriesLongerThan($db, $seconds) {
        $queries = array();
        foreach (self::getStack($db) as $tmp) {
            if ($tmp->getExecutionTime() > $seconds) {
                $queries[] = $tmp;
            }
        }

        return $queries;
    }

}
