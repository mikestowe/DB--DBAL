<?php
interface DB_Inc_Cache_Interface {
    public function getCache($db, $table, $key);

    public function setCache($db, $table, $key, $data);

    public function clearCache($db, $table);

    public function setPrimaryKey($db, $table, $column);

    public function getPrimaryKey($db, $table);

    public function setFileStatus($db, $table, $status);

    public function getFileStatus($db, $table);
}
