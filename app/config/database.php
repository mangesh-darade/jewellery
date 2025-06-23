<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['dsn']      The full DSN string describe a connection to the database.
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database driver. e.g.: mysqli.
|			Currently supported:
|				 cubrid, ibase, mssql, mysql, mysqli, oci8,
|				 odbc, pdo, postgre, sqlite, sqlite3, sqlsrv
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Query Builder class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['encrypt']  Whether or not to use an encrypted connection.
|	['compress'] Whether or not to use client compression (MySQL only)
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|	['failover'] array - A array with 0 or more data for connections if the main should fail.
|	['save_queries'] TRUE/FALSE - Whether to "save" all executed queries.
| 				NOTE: Disabling this will also effectively disable both
| 				$this->db->last_query() and profiling of DB queries.
| 				When you run a query, with this setting set to TRUE (default),
| 				CodeIgniter will store the SQL statement for debugging purposes.
| 				However, this may cause high memory usage, especially if you run
| 				a lot of SQL queries ... disable this to avoid that problem.
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $query_builder variables lets you determine whether or not to load
| the query builder class.
*/

$active_group = 'default';
$query_builder = TRUE;
$subdomain = explode('.', $_SERVER['HTTP_HOST'])[0];
switch ($subdomain) {
	case '1602clothingdev':
		$db['default'] = array(
			'dsn'	   => '',
			'hostname' => 'localhost',
			'username' => '16.02_Clothing_to_16.01',
			'password' => '16.02_Clothing_to_16.01',
			'database' => '16.02_Clothing_to_16.01',
			'dbdriver' => 'mysqli',
			'dbprefix' => 'sma_',
			'pconnect' => FALSE,
			'db_debug' => TRUE,
			'cache_on' => FALSE,
			'cachedir' => '',
			'char_set' => 'utf8',
			'dbcollat' => 'utf8_general_ci',
			'swap_pre' => '',
			'encrypt'  => FALSE,
			'compress' => FALSE,
			'stricton' => FALSE,
			'failover' => array(),
			'save_queries' => FALSE
		);
	break;

	case 'localhost':
		$db['default'] = array(
			'dsn'	   => '',
			'hostname' => 'localhost',
			'username' => 'root',
			'password' => '',
			'database' => '16.02_Clothing_to_16.01',
			'dbdriver' => 'mysqli',
			'dbprefix' => 'sma_',
			'pconnect' => FALSE,
			'db_debug' => TRUE,
			'cache_on' => FALSE,
			'cachedir' => '',
			'char_set' => 'utf8',
			'dbcollat' => 'utf8_general_ci',
			'swap_pre' => '',
			'encrypt'  => FALSE,
			'compress' => FALSE,
			'stricton' => FALSE,
			'failover' => array(),
			'save_queries' => FALSE
		);
	break;
		
		
		case '1602clothingprod':
		$db['default'] = array(
			'dsn'	   => '',
			'hostname' => 'localhost',
			'username' => 'stadmin_MagicWearPROD_To_1602DB',
			'password' => 'Jxds83?20',
			'database' => 'sitadmin_MagicWearPROD_To_1602DB',
			'dbdriver' => 'mysqli',
			'dbprefix' => 'sma_',
			'pconnect' => FALSE,
			'db_debug' => TRUE,
			'cache_on' => FALSE,
			'cachedir' => '',
			'char_set' => 'utf8',
			'dbcollat' => 'utf8_general_ci',
			'swap_pre' => '',
			'encrypt'  => FALSE,
			'compress' => FALSE,
			'stricton' => FALSE,
			'failover' => array(),
			'save_queries' => FALSE
		);
	break;
		
		case 'magicwearssgr':
		$db['default'] = array(
			'dsn'	   => '',
			'hostname' => 'localhost',
			'username' => 'sitadmin_magsgr56',
		    'password' => 'L6~oh98w0',
		    'database' => 'sitadmin_Magicsgr',
			'dbdriver' => 'mysqli',
			'dbprefix' => 'sma_',
			'pconnect' => FALSE,
			'db_debug' => TRUE,
			'cache_on' => FALSE,
			'cachedir' => '',
			'char_set' => 'utf8',
			'dbcollat' => 'utf8_general_ci',
			'swap_pre' => '',
			'encrypt'  => FALSE,
			'compress' => FALSE,
			'stricton' => FALSE,
			'failover' => array(),
			'save_queries' => FALSE
		);
	break;
		
		case 'magicwears':
		$db['default'] = array(
			'dsn'	   => '',
			'hostname' => 'localhost',
			'username' => 'sitadmin_mag789',
			'password' => '4@qG0c01w',
			'database' => 'sitadmin_Magicwe',
			'dbdriver' => 'mysqli',
			'dbprefix' => 'sma_',
			'pconnect' => FALSE,
			'db_debug' => TRUE,
			'cache_on' => FALSE,
			'cachedir' => '',
			'char_set' => 'utf8',
			'dbcollat' => 'utf8_general_ci',
			'swap_pre' => '',
			'encrypt'  => FALSE,
			'compress' => FALSE,
			'stricton' => FALSE,
			'failover' => array(),
			'save_queries' => FALSE
		);
	break;

		case 'aarvisales':
		$db['default'] = array(
			'dsn'	   => '',
			'hostname' => 'localhost',
			'username' => 'stadmin_aarvi',
			'password' => 'Tdp*v6011',
			'database' => 'sitadmin_aarvisls',
			'dbdriver' => 'mysqli',
			'dbprefix' => 'sma_',
			'pconnect' => FALSE,
			'db_debug' => TRUE,
			'cache_on' => FALSE,
			'cachedir' => '',
			'char_set' => 'utf8',
			'dbcollat' => 'utf8_general_ci',
			'swap_pre' => '',
			'encrypt'  => FALSE,
			'compress' => FALSE,
			'stricton' => FALSE,
			'failover' => array(),
			'save_queries' => FALSE
		);
	break;
		
		case 'restaurantdemo':
		
$db['default'] = array(
	'dsn'	   => '',
	'hostname' => 'localhost',
	'username' => 'stadmin_restdm',
	'password' => '9Bcu6#w29',
	'database' => 'sitadmin_restdemo',
	'dbdriver' => 'mysqli',
	'dbprefix' => 'sma_',
	'pconnect' => FALSE,
	'db_debug' => TRUE,
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt'  => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => FALSE
	
	
	); 
		
 break;
		
	case 'restaurantdemo':
		
$db['default'] = array(
	'dsn'	   => '',
	'hostname' => 'localhost',
	'username' => 'stadmin_restdm',
	'password' => '9Bcu6#w29',
	'database' => 'sitadmin_restdemo',
	'dbdriver' => 'mysqli',
	'dbprefix' => 'sma_',
	'pconnect' => FALSE,
	'db_debug' => TRUE,
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt'  => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => FALSE
	
	

	
	
); 
		
 break;



 
}