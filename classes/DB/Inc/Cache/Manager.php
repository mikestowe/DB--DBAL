<?php
class DB_Inc_Cache_Manager {

    const keyPrefix = 'DB_DBAL_CACHE_';
    private $_doCache = false;
    private $_instance;
    private $_cache = array();
    private $_files = array();
    private $_pkeys = array();

    public function __construct() {
        if (function_exists('apc_fetch')) {
            $this->_doCache = true;
        }
    }

    private function _doCache() {
        if (is_null($this->_doCache)) {
            $this->_doCache = false;
            if (function_exists('apc_fetch')) {
                $this->_getCacheSystem('APC');

            }
        }

        return $this->_doCache;
    }

    private function _getCacheSystem($name) {
        require_once ('Interface.php');
        require_once ($name . '.php');

        $name = 'DB_Inc_Cache_' . $name;
        $this->_instance = new $name;

        $this->_doCache = true;
    }

    public function useCache($name) {
        $this->_getCacheSystem($name);
        return $this;
    }

    public function getCache($db, $table, $key) {
        if (!$this->_doCache()) {
            if (isset($this->_cache[$db][$table][$key])) {
                return $this->_cache[$db][$table][$key];
            }
            return false;
        }

        return $this->_instance->getCache($db, $table, $key);
    }

    public function setCache($db, $table, $key, $data) {
        if (!$this->_doCache()) {
            $this->_cache[$db][$table][$key] = $data;
            return false;
        }

        return $this->_instance->setCache($db, $table, $key, $data);
    }

    public function clearCache($db, $table) {
        if (!$this->_doCache()) {
            if (isset($this->_cache[$db][$table])) {
                unset($this->_cache[$db][$table]);
                return;
            }
            return false;
        }

        return $this->_instance->clearCache($db, $table);
    }

    public function setPrimaryKey($db, $table, $column) {
        if (!$this->_doCache()) {
            $this->_pkeys[$db][$table] = $column;
            return false;
        }

        return $this->_instance->setPrimaryKey($db, $table, $column);
    }

    public function getPrimaryKey($db, $table) {
        if (!$this->_doCache()) {
            if (isset($this->_pkeys[$db][$table])) {
                return $this->_pkeys[$db][$table];
            }
            return false;
        }

        return $this->_instance->getPrimaryKey($db, $table);
    }

    public function setFileStatus($db, $table, $status) {
        if (!$this->_doCache()) {
            $this->_files[$db][$table] = $status;
            return false;
        }

        return $this->_instance->setFileStatus($db, $table, $status);
    }

    public function getFileStatus($db, $table) {
        if (!$this->_doCache()) {
            if (isset($this->_files[$db][$table])) {
                return $this->_files[$db][$table];
            }
            return false;
        }

        return $this->_instance->getFileStatus($db, $table);
    }

    public function makeKey($input) {
        return md5($input);
    }

    public function __call($function, $args) {
        return call_user_func_array(array($this->_instance, $function), $args);
    }

}
