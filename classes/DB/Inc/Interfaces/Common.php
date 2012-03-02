<?php
interface DB_Inc_Interfaces_Common {
    public function table($table, $shortname);

    public function select();
    public function insert(array $data);
    public function update(array $data);
    public function delete();
    public function raw($query);

    public function join($table, $nicename, $on, $type);
    public function innerjoin($table, $nicename, $on);
    public function leftjoin($table, $nicename, $on);
    public function rightjoin($table, $nicename, $on);
    //public function union();

    public function where();
    public function andWhere();
    public function orWhere();
    //public function whereIn();
    //public function andWhereIn();
    //public function orWhereIn();

    public function groupBy($orderby);
    public function orderBy($groupby);

    public function limit($start, $records);

}
