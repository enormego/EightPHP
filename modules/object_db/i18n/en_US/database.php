<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'invalid_configuation'  => 'A database driver and name must be defined in your configuration array',
	'invalid_query'         => 'All queries must be strings before they are executed',
	'error'                 => '#%s: There was an SQL error: %s',
	'connection'            => '#%s: There was an error connecting to the database: %s',


	'invalid_dsn'           => 'The DSN you supplied is not valid: %s',
	'must_use_set'          => 'You must set a SET clause for your query.',
	'must_use_where'        => 'You must set a WHERE clause for your query.',
	'must_use_table'        => 'You must set a database table for your query.',
	'table_not_found'       => 'Table %s does not exist in your database.',
	'not_implemented'       => 'The method you called, %s, is not supported by this driver.',
	'result_read_only'      => 'Query results are read only.'
);