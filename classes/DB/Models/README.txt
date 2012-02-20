DB::DBAL Models ReadMe
(c) 2012, version 1
February 20, 2012


:: MODELS AND ACTIONS


Models are where event driven functions can be stored to provide universal functionality when performing queries on specific tables.

These events include:
- preFetch : before fetching records through a select query
- postFetch : after fetching records through a select query
- preInsert : before INSERTING data into the table
- postInsert : after INSERTING data into the table
- preUpdate : before UPDATING data in a table
- postUpdate : after UPDATING data in a table
- preSave : before inserting or updating data in a table
- postSave : after inserting or updating data in a table



:: FILE PATH

Models are divided by the Database type (mysql, mssql, etc), the database name, and finally the table name.

Our example model is for the MySQL Database "DBAL2" and applies to the table "cow."  It is important that the path follows this strict naming convention:

DB/Models/(lowercase database type)/(lowercase database name)/(lowercase table name)Table.php



:: CLASS NAME

For the actual class, the case sensitivity should be identical to the database name (so if it was uppercase DBAL2 the classname should use uppercase DBAL2 even though the file path uses the lowercase).

Model classes should follow this strict naming convention:

DB_Models_(Database Type)_(Database Name)_(lowercase table name)Table

Models must also extend the DB_Inc_Abstracts_Models class as shown in the example model.



:: OTHER

Note that model methods are called automatically.  If a Model does not exist or does not contain a method for a specific action the DBAL will simply skip performing that action and continue normally.

Within the model method database connections are remembered.  YOu may access any previously declared connection, or create a new one and perform any database action just as you would from your script (ie: DB::MySQL()->...).  However, database memory from within the Model Method is local, and once the method is exited the script will resume with the original database used to trigger to event originally.