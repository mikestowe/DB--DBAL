<?php
interface DB_Inc_Interfaces_Database {
    public function init();
    public function connect();
    public function selectDB($database);
    public function quote($input);
    public function getPrimaryKey();

    public function error();
    public function doFetch();
    public function execute($getOldData);
    public function doRawQuery($query);

    // Transactions
    public function startTransaction();
    public function rollback();
    public function commit();

}
