<?php
// Require the DB Class
require_once('classes/DB.php');

// Setup a Connection (saving it is optional)
DB::MySQL('localhost','root','')->saveConnectionAs('default');

DB::MySQL()->profilerStart();

// Find Using Magic Find Method
$a = DB::MySQL()->db('DBAL')->table('test')->findByName('Bob');
echo $a[0]->name . '<hr />';

// Select a Database, Table, and Query It
$a = DB::MySQL()->db('DBAL')->table('test')->select()->where('1=1')->fetchOne(); // fetchOne retains state

// Show the Query SQL
echo $a->querySQL();
echo '<hr />';

// Get a Property
echo $a->name;
echo '<hr />';

// Change Value
$a->name = 'Mikes';

// Save
$a->save();

// Find out what changed
var_dump($a->changedData());
echo '<hr />';

// Get Old Data
var_dump($a->oldData());
echo '<hr />';

// Affected Rows
echo "Affected Rows: " . $a->affectedRows();
echo '<hr />';

// Switch Database, ReQuery
$b = $a->db('DBAL2')->table('cow')->select()->fetchOne();

// Get Property
echo $b->name;

// Edit Value
$b->name = 'Rogers';

// Save
$b->save();

// Here's what just happened:
echo $b->querySql();
echo '<hr />';

// Ok, let's get all the records from the cow database
$c = $b->limit(0)->fetchAll();

// And the count of $b
echo "Records: " . $b->count();
echo '<hr />';

// And Our Query :)
echo $b->querySQL();
echo '<hr />';

// Now let's loop through and add 's' to everyone's name and echo the query
foreach($c as $v) {
	echo $v->name . '<br />';
	$v->name .= 's';
	$v->save();
	echo $v->querySql();
	echo '<hr />';
}

// Now let's add a name
DB::MySQL()->table('cow')->insert(array('name' => 'Mike'))->save();

DB::MySQL()->profilerEnd();

echo '<hr />';
echo 'LARGE QUERIES:<br />';
echo 'QUERY | TIME TAKEN | MEMORY USED<br />';
foreach(DB::MySQL()->profilerShowLargerThan(1000) as $profile) {
    echo $profile->getQuery();
    echo ' | ';
    echo $profile->getExecutionTime();
    echo ' | ';
    echo $profile->getMemoryUsed();
    echo '<br />';
}

echo '<hr />';
echo 'LONG QUERIES:<br />';
echo 'QUERY | TIME TAKEN | MEMORY USED<br />';
foreach(DB::MySQL()->profilerShowLongerThan(.01) as $profile) {
    echo $profile->getQuery();
    echo ' | ';
    echo $profile->getExecutionTime();
    echo ' | ';
    echo $profile->getMemoryUsed();
    echo '<br />';
}

echo '<hr />';
echo 'ALL QUERIES:<br />';
echo 'QUERY | TIME TAKEN | MEMORY USED<br />';
foreach(DB::MySQL()->profilerShow() as $profile) {
    echo $profile->getQuery();
    echo ' | ';
    echo $profile->getExecutionTime();
    echo ' | ';
    echo $profile->getMemoryUsed();
    echo '<br />';
}
?>
