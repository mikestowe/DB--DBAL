<?php
/**
 * Database Abstract Layer / Data Mapper
 *
 * In other words, I don't have a cool name for it yet
 *
 * @author Michael Stowe
 * @link http://www.mikestowe.com
 * @copyright 2012, All Rights Reserved
 * @version 1.0
 */

class DB {
    private static $_instance;
    private $_connections = array();
    private $_cache;
    private $_profiler;

    public function __construct() {
        require_once ('DB/Inc/Cache/Manager.php');
        $this->_cache = new DB_Inc_Cache_Manager();
    }

    public function saveConnection($function, $name, $link) {
        $this->_connections[$function]['connections'][$name] = $link;
    }

    public function getConnection($function, $name) {
        return $this->_connections[$function]['connections'][$name];
    }

    public function getConnections($function) {
        return $this->_connections[$function]['connections'];
    }

    public function removeConnection($function, $name) {
        if ($name == 'active') {
            $this->saveConnection($function, 'active', false);
            return;
        }

        unset($this->_connections[$function]['connections'][$name]);
    }

    public function __call($function, $parameters) {
        $className = 'DB_' . $function;
        if (!isset($this->_connections[$function])) {
            require_once ('DB/Inc/Interfaces/Common.php');
            require_once ('DB/Inc/Interfaces/Database.php');
            require_once ('DB/Inc/Abstracts/DataHandler.php');
            require_once ('DB/Inc/Abstracts/Internals.php');
            require_once ('DB/Inc/Abstracts/Common.php');
            require_once ('DB/Inc/Abstracts/Models.php');
            require_once ('DB/' . $function . '.php');

            $this->_connections[$function] = array();
            $this->saveConnection($function, 'active', false);
            $this->_connections[$function]['resource'] = new $className($function);

            if (count($parameters[0])) {
                $this->_connections[$function]['resource']->connect($parameters[0]);
            }
        }

        $c = $this->_connections[$function]['resource'];
        $r = new $className($function, $c->getConnection(), $c->getDatabase());
        $r->init();
        return $r;
    }

    public static function getInstance() {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public static function cacheManager() {
        return self::getInstance()->_cache;
    }

    public static function profiler() {
        $self = self::getInstance();
        if (is_null($self->_profiler)) {
            require_once ('DB/Inc/Profiler/Profiler.php');
            require_once ('DB/Inc/Profiler/Data.php');
            $self->_profiler = new DB_Inc_Profiler_Profiler();
        }
        return $self->_profiler;
    }

    public static function getPath() {
        return __DIR__;
    }

    public static function __callStatic($function, $parameters) {
        return self::getInstance()->$function($parameters);
    }

}
