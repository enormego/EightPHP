<?php
/**
 * @package		System
 * @subpackage	Libraries
 *
 * Sets default routing, allowing up to 3 segments to be used.
 *
 *     $config['default'] = array
 *     (
 *         // Default routing
 *         :controller/:method/:id',
 *         
 *         // Defaults for route keys
 *         'controller' => 'welcome',
 *         'method' => 'index',
 *     );
 *
 * The converted regex for this route is:
 *
 *     (?:([^/]++)(?:/([^/]++)(?:/([^/]++))?)?)?
 *
 * To define a specific pattern for a key, you can use the special "regex" key:
 *
 *     $config['default'] = array
 *     (
 *         // Limit the controller to letters and underscores
 *         'regex' => array('controller' => '[a-z_]+'),
 *     );
 *
 * To add a prefix to any key, you can use the special "prefix" key:
 *
 *     $config['admin'] = array
 *     (
 *         'admin/:controller/:method/:id',
 *         
 *         // Will change all controllers to admin_:controller
 *         'prefix' => array('controller' => 'admin_'),
 *     );
 *
 */
$config['default'] = array
(
	// Default routing
	':controller/:method/:id',

	// Defaults for route keys
	'controller' => 'welcome',
	'method' => 'index',
);
