<?php
class DB_Models_MySQL_DBAL2_cowTable extends DB_Inc_Abstracts_Models {
    public function setup() {
        // This Method can be used for important table information

        // Let's setup a PrimaryKey (in case the DB can't)
        $this->setPrimaryKey('id');
    }

    public function postFetch() {
        $a = DB::MySQL()->db('DBAL')->table('test')->select()->where('1=1')->fetchOne();
        $a->name = 'Bobby';
        $a->save();

        $this->name = 'Joe';
    }

}
