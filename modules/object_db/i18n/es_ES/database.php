<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'invalid_configuation'  => 'Debes definir un driver de base de datos y un nombre en el array de configuración',
	'invalid_query'         => 'Todas las consultas deben de ser cadenas antes de ser ejecutadas',
	'error'                 => '#%s: Ha ocurrido un error de SQL: %s',
	'connection'            => '#%s: Ha ocurrido un error conectando a la base de datos: %s',


	'invalid_dsn'           => 'El DSN provisto no es valido: %s',
	'must_use_set'          => 'Debes de proveer una clausula SET para la consulta.',
	'must_use_where'        => 'Debes de proveer una clausula WHERE para la consulta.',
	'must_use_table'        => 'Debes de proveer una tabla de la base de datos para la consulta.',
	'table_not_found'       => 'La tabla %s no existe en la base de datos.',
	'not_implemented'       => 'El metodo, %s, no esta soportado por este driver.',
	'result_read_only'      => 'Los resultados de la consulta son de solo lectura.'
);